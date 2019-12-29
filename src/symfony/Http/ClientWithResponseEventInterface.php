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

namespace Teknoo\East\FoundationBundle\Http;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Teknoo\East\Foundation\Http\ClientInterface;

/**
 * Interface NeedResponseEventInterface to complete Teknoo\East\Foundation\Http\ClientInterface to define a method
 * to register the RequestEvent instance into the client via the KernelListener and update it following the
 * Kernel loop to keep a client usable with Symfony.
 *
 * @see http://symfony.com/doc/current/components/http_kernel.html
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface ClientWithResponseEventInterface extends ClientInterface
{
    /**
     * To register the RequestEvent instance into the client via the KernelListener.
     *
     * @param RequestEvent $requestEvent
     *
     * @return self
     */
    public function setRequestEvent(RequestEvent $requestEvent): ClientWithResponseEventInterface;
}
