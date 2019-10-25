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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Router;

use Teknoo\East\Foundation\Middleware\MiddlewareInterface;

/**
 * Interface RouterInterface is a contract to create router to check if a request is runnable by one of its
 * controller and pass it to the selected controller.
 *
 * The router must define the request the result and use the key defined by RouterInterface::ROUTER_RESULT_KEY
 *
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface RouterInterface extends MiddlewareInterface
{
    public const ROUTER_RESULT_KEY = '_east_route';
    public const MIDDLEWARE_PRIORITY = 10;
}
