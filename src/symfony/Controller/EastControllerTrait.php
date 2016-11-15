<?php
/**
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
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\FoundationBundle\Controller;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zend\Diactoros\Response;

/**
 * Class Controller.
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait EastControllerTrait
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var TwigEngine
     */
    protected $templating;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @param RouterInterface $router
     *
     * @return EastControllerTrait
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * @param TwigEngine $templating
     *
     * @return EastControllerTrait
     */
    public function setTemplating(TwigEngine $templating)
    {
        $this->templating = $templating;

        return $this;
    }

    /**
     * @param \Twig_Environment $twig
     *
     * @return EastControllerTrait
     */
    public function setTwig(\Twig_Environment $twig)
    {
        $this->twig = $twig;

        return $this;
    }

    /**
     * @param TokenStorageInterface $tokenStorage
     *
     * @return EastControllerTrait
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage)
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
     *
     * @return EastControllerTrait
     */
    protected function redirect(ClientInterface $client, $url, $status = 302)
    {
        $client->responseFromController(new Response\RedirectResponse($url, $status));

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
     * @return EastControllerTrait
     */
    protected function redirectToRoute(
        ClientInterface $client,
        string $route,
        array $parameters = array(),
        int $status = 302
    ) {
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
        if ($this->templating instanceof TwigEngine) {
            return $this->templating->render($view, $parameters);
        }

        if (!$this->twig instanceof \Twig_Environment) {
            throw new \LogicException('You can not use the "renderView" method if the Templating Component or the Twig Bundle are not available.');
        }

        return $this->twig->render($view, $parameters);
    }

    /**
     * Renders a view.
     *
     * @param ClientInterface $client
     * @param string          $view       The view name
     * @param array           $parameters An array of parameters to pass to the view
     *
     * @return EastControllerTrait
     */
    protected function render(ClientInterface $client, string $view, array $parameters = array())
    {
        $htmlBody = $this->renderView($view, $parameters);

        $client->responseFromController(new Response\HtmlResponse($htmlBody));

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
     * @return AccessDeniedException
     */
    protected function createAccessDeniedException(
        string $message = 'Access Denied.',
        \Exception $previous = null
    ): AccessDeniedException {
        return new AccessDeniedException($message, $previous);
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
