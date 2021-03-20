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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
