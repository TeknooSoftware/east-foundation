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

namespace Teknoo\East\Foundation\Processor;

use Teknoo\East\Foundation\Middleware\MiddlewareInterface;

/**
 * Processor is a specific middleware able to read the result in the request computed by the router.
 *
 * If some arguments are missing in the request. The processor must throws exceptions.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface ProcessorInterface extends MiddlewareInterface
{
    final public const MIDDLEWARE_PRIORITY = 15;

    final public const WORK_PLAN_CONTROLLER_KEY = 'controller';
}
