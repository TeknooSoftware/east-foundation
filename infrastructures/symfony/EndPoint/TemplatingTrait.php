<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\FoundationBundle\EndPoint;

use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;
use Teknoo\East\Foundation\EndPoint\RenderingInterface;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Throwable;

/**
 * Trait to help developer to write endpoint with Symfony (also called controller) and reuse Symfony components
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait TemplatingTrait
{
    use ResponseFactoryTrait;

    protected ?EngineInterface $templating = null;

    protected StreamFactoryInterface $streamFactory;

    /*
     * To inject the template engine to render views.
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


    /*
     * Renders a view.
     */
    public function render(
        ClientInterface $client,
        string $view,
        array $parameters = array(),
        int $status = 200,
        array $headers = []
    ): RenderingInterface {
        if (!$this->templating instanceof EngineInterface) {
            $client->errorInRequest(new RuntimeException('Missing template engine'));

            return $this;
        }

        $response = $this->responseFactory->createResponse($status);
        $headers['content-type'] = 'text/html; charset=utf-8';

        $response = $this->addHeadersIntoResponse($response, $headers);
        $stream = $this->streamFactory->createStream();

        $this->templating->render(
            new Promise(
                static function (ResultInterface $result) use ($stream, $client, $response) {
                    if ($stream instanceof CallbackStreamInterface) {
                        $stream->bind(static fn() => (string) $result);
                    } else {
                        $stream->write((string) $result);
                    }

                    $response = $response->withBody($stream);

                    $client->acceptResponse($response);
                },
                static function (Throwable $error) use ($client) {
                    $client->errorInRequest($error);
                }
            ),
            $view,
            $parameters
        );

        return $this;
    }
}
