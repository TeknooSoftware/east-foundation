<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\FoundationBundle\EndPoint;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Templating\EngineInterface;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zend\Diactoros\CallbackStream;
use Zend\Diactoros\Response;

/**
 * Trait to help developer to write endpoint with Symfony (also called controller) and reuse Symfony component like
 * router or twig engine?
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait EastEndPointTrait
{
    protected ?RouterInterface $router = null;

    protected ?EngineInterface $templating = null;

    protected ?TokenStorageInterface $tokenStorage = null;

    /**
     * To inject the router into the trait, needed to generate url.
     *
     * @param RouterInterface $router
     *
     * @return EastEndPointTrait
     */
    public function setRouter(RouterInterface $router): self
    {
        $this->router = $router;

        return $this;
    }

    /**
     * To inject the Twig engine to render views.
     *
     * @param EngineInterface $templating
     *
     * @return EastEndPointTrait
     */
    public function setTemplating(EngineInterface $templating): self
    {
        $this->templating = $templating;

        return $this;
    }

    /**
     * To inject the Token storage to extract users from session or token.
     *
     * @param TokenStorageInterface $tokenStorage
     *
     * @return EastEndPointTrait
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage): self
    {
        $this->tokenStorage = $tokenStorage;

        return $this;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string $route         The name of the route
     * @param mixed  $parameters    An array of parameters
     * @param int    $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @see UrlGeneratorInterface
     */
    protected function generateUrl(
        string $route,
        $parameters = array(),
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        if (!$this->router instanceof RouterInterface) {
            throw new \LogicException('The router is not registered in your application.');
        }

        return $this->router->generate($route, $parameters, $referenceType);
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param ClientInterface $client
     * @param string          $url    The URL to redirect to
     * @param int             $status The status code to use for the Response
     * @param array<string, mixed> $headers An array of values to inject into HTTP header response
     *
     * @return EndPointInterface
     */
    public function redirect(
        ClientInterface $client,
        string $url,
        int $status = 302,
        array $headers = []
    ): EndPointInterface {
        $client->acceptResponse(new Response\RedirectResponse($url, $status, $headers));

        return $this;
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @param ClientInterface $client
     * @param string          $route      The name of the route
     * @param array           $parameters An array of parameters
     * @param int             $status     The status code to use for the Response
     *
     * @return EndPointInterface
     */
    protected function redirectToRoute(
        ClientInterface $client,
        string $route,
        array $parameters = array(),
        int $status = 302
    ): self {
        return $this->redirect($client, $this->generateUrl($route, $parameters), $status);
    }

    /**
     * Returns a rendered view.
     *
     * @param string $view       The view name
     * @param array  $parameters An array of parameters to pass to the view
     *
     * @return string The rendered view
     */
    protected function renderView(string $view, array $parameters = array()): string
    {
        if ($this->templating instanceof EngineInterface) {
            return $this->templating->render($view, $parameters);
        }

        throw new \LogicException(
            'You can not use the "renderView" method if the Templating Component or the '
            . 'Twig Bundle are not available.'
        );
    }

    /**
     * Renders a view.
     *
     * @param ClientInterface $client
     * @param string          $view       The view name
     * @param array           $parameters An array of parameters to pass to the view
     * @param int             $status The status code to use for the Response
     * @param array<string, mixed> $headers An array of values to inject into HTTP header response
     *
     * @return EndPointInterface
     */
    public function render(
        ClientInterface $client,
        string $view,
        array $parameters = array(),
        int $status = 200,
        array $headers = []
    ): EndPointInterface {
        $client->acceptResponse(
            new Response\HtmlResponse(
                new CallbackStream(function () use ($view, $parameters) {
                    return $this->renderView($view, $parameters);
                }),
                $status,
                $headers
            )
        );

        return $this;
    }

    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     *     throw $this->createNotFoundException('Page not found!');
     *
     * @param string          $message  A message
     * @param \Exception|null $previous The previous exception
     *
     * @return NotFoundHttpException
     */
    protected function createNotFoundException(
        string $message = 'Not Found',
        \Exception $previous = null
    ): NotFoundHttpException {
        return new NotFoundHttpException($message, $previous);
    }

    /**
     * Returns an AccessDeniedException.
     *
     * This will result in a 403 response code. Usage example:
     *
     *     throw $this->createAccessDeniedException('Unable to access this page!');
     *
     * @param string          $message  A message
     * @param \Exception|null $previous The previous exception
     *
     * @return AccessDeniedHttpException
     */
    protected function createAccessDeniedException(
        string $message = 'Access Denied.',
        \Exception $previous = null
    ): AccessDeniedHttpException {
        return new AccessDeniedHttpException($message, $previous);
    }

    /**
     * Get a user from the Security Token Storage.
     *
     * @return mixed
     *
     * @throws \LogicException If SecurityBundle is not available
     *
     * @see TokenInterface::getUser()
     */
    protected function getUser()
    {
        if (!$this->tokenStorage instanceof TokenStorageInterface) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        /*
         * @var TokenInterface
         */
        if (!is_callable([$token, 'getUser']) || !is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }
}
