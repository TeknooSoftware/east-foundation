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
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface ClientInterface extends BaseClientInterface
{
}
