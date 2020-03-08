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

use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Templating\EngineInterface;
use Teknoo\East\Foundation\EndPoint\RenderingInterface;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;
use Teknoo\East\Foundation\Http\ClientInterface;

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
trait TemplatingTrait
{
    use ResponseFactoryTrait;

    protected ?EngineInterface $templating = null;

    protected StreamFactoryInterface $streamFactory;

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

    public function setStreamFactory(StreamFactoryInterface $streamFactory): self
    {
        $this->streamFactory = $streamFactory;

        return $this;
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
     * @return RenderingInterface
     */
    public function render(
        ClientInterface $client,
        string $view,
        array $parameters = array(),
        int $status = 200,
        array $headers = []
    ): RenderingInterface {
        $response = $this->responseFactory->createResponse($status);
        $headers['content-type'] = 'text/html; charset=utf-8';

        $response = $this->addHeadersIntoResponse($response, $headers);
        $stream = $this->streamFactory->createStream();

        if ($stream instanceof CallbackStreamInterface) {
            $stream->bind(function () use ($view, $parameters) {
                return $this->renderView($view, $parameters);
            });
        } else {
            $stream->write($this->renderView($view, $parameters));
        }

        $response = $response->withBody($stream);

        $client->acceptResponse($response);

        return $this;
    }
}
