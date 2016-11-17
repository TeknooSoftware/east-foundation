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

namespace Teknoo\East\FoundationBundle\Http;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Teknoo\East\Foundation\Http\ClientInterface;

/**
 * Interface NeedResponseEventInterface to complete Teknoo\East\Foundation\Http\ClientInterface to define a method
 * to register the GetResponseEvent instance into the client via the KernelListener.
 *
 * @package Teknoo\East\FoundationBundle\Http
 */
interface ClientWithResponseEventInterface extends ClientInterface
{
    /**
     * To register the GetResponseEvent instance into the client via the KernelListener.
     *
     * @param GetResponseEvent $getResponseEvent
     * @return self
     */
    public function setGetResponseEvent(GetResponseEvent $getResponseEvent): ClientWithResponseEventInterface;
}