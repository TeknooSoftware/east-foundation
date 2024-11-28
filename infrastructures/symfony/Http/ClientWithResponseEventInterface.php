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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface ClientWithResponseEventInterface extends ClientInterface
{
    /*
     * To register the RequestEvent instance into the client via the KernelListener.
     */
    public function setRequestEvent(RequestEvent $requestEvent): ClientWithResponseEventInterface;
}
