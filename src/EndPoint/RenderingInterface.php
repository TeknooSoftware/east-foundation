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
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\EndPoint;

use Teknoo\East\Foundation\Client\ClientInterface;

/**
 * To define an endpoint able to render a HTML page thanks to a template engine
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface RenderingInterface
{
    /**
     * Renders a view via a template engine like Twig or another system.
     *
     * @param array<string, mixed> $parameters An array of parameters to pass to the view
     * @param int                  $status The status code to use for the Response
     * @param array<string, mixed> $headers An array of values to inject into HTTP header response
     */
    public function render(
        ClientInterface $client,
        string $view,
        array $parameters = [],
        int $status = 200,
        array $headers = []
    ): RenderingInterface;
}
