<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\FoundationBundle\Router;

use Psr\Http\Message\MessageInterface;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SfAbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Teknoo\East\Foundation\Router\Result;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\East\Foundation\Router\RouterInterface;

use function array_flip;
use function explode;
use function implode;
use function is_callable;
use function is_string;
use function preg_match;
use function str_starts_with;
use function str_contains;
use function strtolower;
use function substr;

/**
 * Class Router to check if a request is runnable by one of its controller and pass it to the selected controller.
 * This router reuse the Symfony matcher component to find controller and routes to use. Only controller as service
 * (The matcher returns a callable and not the controller's identifier Controller::Action). If the controller is not
 * a callable, this router ignores the route.
 *
 * All public method of the manager must only return the self client or a clone instance.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Router implements RouterInterface
{
    private ?string $excludePathsRegex = null;

    /**
     * @param array<int, string> $excludePaths
     */
    public function __construct(
        private readonly UrlMatcherInterface $matcher,
        private readonly ContainerInterface $container,
        array $excludePaths = [],
    ) {
        if (!empty($excludePaths)) {
            $this->excludePathsRegex = '#^(' . implode('|', $excludePaths) . ')#S';
        }
    }

    private function cleanSymfonyHandler(string $path): string
    {
        if (str_starts_with($path, '/app.php')) {
            return substr($path, 8);
        }

        if (str_starts_with($path, '/app_dev.php')) {
            return substr($path, 12);
        }

        if (str_starts_with($path, '/index.php')) {
            return substr($path, 10);
        }

        return $path;
    }

    /*
     * Method to find the controller to call for this method via the Symfony Matcher. Return only controller as service
     * (callable provided by the Symfony matcher), ignore other.
     */
    private function matchRequest(ServerRequestInterface $request): ?callable
    {
        $parameters = [];
        $path = $this->cleanSymfonyHandler(
            (string) $request->getUri()->getPath()
        );

        if (null !== $this->excludePathsRegex && preg_match($this->excludePathsRegex, $path)) {
            return null;
        }

        try {
            $parameters = $this->matcher->match($path);
        } catch (ResourceNotFoundException) {
            /* Do nothing, keep the framework to manage it */
        }

        if (empty($parameters['_controller'])) {
            return null;
        }

        $controller = $parameters['_controller'];
        if (!($isCallable = is_callable($controller)) && !$this->container->has($controller)) {
            return null;
        }

        if ($isCallable && is_string($controller)) {
            if (str_contains($controller, '::')) {
                /** @var array{class-string, string} $explodedController */
                $explodedController = explode('::', $controller);

                $reflection = new ReflectionClass((string) $explodedController[0]);
                $isSymfony = $reflection->isSubclassOf(SfAbstractController::class);
                if ($isSymfony) {
                    return null;
                }
            }

            return $controller;
        }

        if ($isCallable) {
            return $controller;
        }

        $entry = $this->container->get($parameters['_controller']);

        $isSymfony = $entry instanceof SfAbstractController;

        if (!is_callable($entry) || $isSymfony) {
            return null;
        }

        return $entry;
    }

    public function execute(
        ClientInterface $client,
        MessageInterface $message,
        ManagerInterface $manager
    ): MiddlewareInterface {
        if (!$message instanceof ServerRequestInterface) {
            return $this;
        }

        $controller = $this->matchRequest($message);

        if (is_callable($controller)) {
            $result = new Result($controller);

            $manager->updateWorkPlan([ResultInterface::class => $result]);
            $manager->updateMessage($message);
        }

        return $this;
    }
}
