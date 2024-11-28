<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\FoundationBundle\Resources\config;

use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Teknoo\East\Diactoros\MessageFactory;
use Teknoo\East\Diactoros\ResponseMessageFactory;

use function DI\get;
use function DI\create;

return [
    ResponseFactory::class => create(),
    ResponseFactoryInterface::class => get(ResponseFactory::class),

    StreamFactory::class => create(),
    StreamFactoryInterface::class => get(StreamFactory::class),

    ServerRequestFactory::class => create(),
    ServerRequestFactoryInterface::class => get(ServerRequestFactory::class),

    UploadedFileFactory::class => create(),
    UploadedFileFactoryInterface::class => get(UploadedFileFactory::class),

    MessageFactory::class => create(),
    ResponseMessageFactory::class => create(),
];
