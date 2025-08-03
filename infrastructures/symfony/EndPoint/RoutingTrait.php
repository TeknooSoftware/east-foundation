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

namespace Teknoo\East\FoundationBundle\EndPoint;

use LogicException;
use Teknoo\East\Foundation\EndPoint\RedirectingInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Trait to help developer to write endpoint with Symfony (also called controller) and reuse Symfony components
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait RoutingTrait
{
    use ResponseFactoryTrait;

    protected ?UrlGeneratorInterface $router = null;

    /*
     * To inject the router into the trait, needed to generate url.
     */
    public function setRouter(UrlGeneratorInterface $router): self
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param array<string, string|int> $parameters
     */
    protected function generateUrl(
        string $route,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        if (!$this->router instanceof UrlGeneratorInterface) {
            throw new LogicException('The router is not registered in your application.');
        }

        return $this->router->generate($route, $parameters, $referenceType);
    }

    /*
     * Returns a RedirectResponse to the given URL.
     */
    public function redirect(
        ClientInterface $client,
        string $url,
        int $status = 302,
        array $headers = []
    ): RedirectingInterface {
        $response = $this->responseFactory->createResponse($status);

        $headers['location'] = $url;
        $response = $this->addHeadersIntoResponse($response, $headers);

        $client->acceptResponse($response);

        return $this;
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @param array<string, int|string> $parameters
     */
    protected function redirectToRoute(
        ClientInterface $client,
        string $route,
        array $parameters = [],
        int $status = 302
    ): RedirectingInterface {
        return $this->redirect($client, $this->generateUrl($route, $parameters), $status);
    }
}
