<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
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

use function explode;
use function implode;
use function is_callable;
use function is_string;
use function json_decode;
use function preg_match;
use function str_starts_with;
use function str_contains;
use function substr;

/**
 * Class Router to check if a request is runnable by one of its controller and pass it to the selected controller.
 * This router reuse the Symfony matcher component to find controller and routes to use. Only controller as service
 * (The matcher returns a callable and not the controller's identifier Controller::Action). If the controller is not
 * a callable, this router ignores the route.
 *
 * All public method of the manager must only return the self client or a clone instance.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
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

    /**
     * @param ServerRequestInterface $request
     * @return array<string, mixed>|null
     * @throws \JsonException
     */
    private function getParameters(ServerRequestInterface &$request): ?array
    {
        $originalPath = $this->cleanSymfonyHandler(
            $request->getUri()->getPath()
        );

        if (null !== $this->excludePathsRegex && preg_match($this->excludePathsRegex, $originalPath)) {
            return null;
        }

        /** @var array<string, mixed> $originalParameters */
        $originalParameters = $this->matcher->match($originalPath);

        if (
            !empty($originalParameters['_controller'])
            || 'ux_live_component' !== ($originalParameters['_route'] ?? '')
            || empty($originalParameters['_live_component'])
        ) {
            return $originalParameters;
        }

        $json = ((array) $request->getParsedBody())['data'] ?? '[]';
        if (!is_string($json)) {
            return $originalParameters;
        }

        $body = json_decode(
            json: $json,
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );

        if (
            !is_array($body)
            || empty($body['props'])
            || !is_array($body['props'])
            || empty($body['props']['originalPath'])
            || !is_string($body['props']['originalPath'])
        ) {
            return $originalParameters;
        }

        $realPath = $this->cleanSymfonyHandler($body['props']['originalPath']);

        /** @var array<string, mixed> $realParameters */
        $realParameters = $this->matcher->match($realPath);
        $realParameters['_live_parameters'] = $originalParameters;
        $realParameters['_live_body'] = $body;

        if (empty($body['updated']) || !is_array($body['updated'])) {
            return $realParameters;
        }

        $attributes = $request->getAttributes();
        foreach ($body['updated'] as $key => $value) {
            if (is_string($key) && !isset($attributes[$key]) & is_string($value)) {
                $request = $request->withAttribute($key, $value);
            }
        }

        return $realParameters;
    }

    /*
     * Method to find the controller to call for this method via the Symfony Matcher. Return only controller as service
     * (callable provided by the Symfony matcher), ignore other.
     */
    private function matchRequest(
        ServerRequestInterface &$request,
    ): ?callable {
        try {
            if (!$parameters = $this->getParameters($request)) {
                return null;
            }
        } catch (ResourceNotFoundException) {
            /* Do nothing, keep the framework to manage it */
        }

        if (empty($parameters['_controller'])) {
            return null;
        }

        if (!empty($parameters['_live_parameters'])) {
            foreach ($parameters as $name => $value) {
                if ('_live_parameters' === $name) {
                    continue;
                }

                $request = $request->withAttribute($name, $value);
            }
        }

        $controller = $parameters['_controller'];
        if (
            !($isCallable = is_callable($controller))
            && (is_string($controller) && !$this->container->has($controller))
        ) {
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

        $entry = null;
        if (is_string($parameters['_controller'])) {
            $entry = $this->container->get($parameters['_controller']);
        }

        $isSymfony = $entry instanceof SfAbstractController;

        if (!is_callable($entry) || $isSymfony) {
            return null;
        }

        return $entry;
    }

    public function execute(
        ClientInterface $client,
        MessageInterface $message,
        ManagerInterface $manager,
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
