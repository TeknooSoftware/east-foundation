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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Http;

use Teknoo\East\Foundation\Client\ClientInterface as BaseClientInterface;

/**
 * ClientInterface is a contract to create object representing the client in the server side. The client must be
 * agnostic and accepts only \Throwable exception and PSR7 response. It's possible to pass a PSR7 Response object
 * without send it via the method "acceptResponse".
 *
 * To update an response, it's mandatory to call the method
 * "updateResponse" and pass a callable able to update the response and update it into the client.
 *
 * The method "sendResponse" as a behavior like updateResponse but send directly the response.
 *
 * All public method of the client must only return the self client or a clone instance.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface ClientInterface extends BaseClientInterface
{
}
