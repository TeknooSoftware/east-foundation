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

namespace Teknoo\East\Foundation\EndPoint;

use Teknoo\East\Foundation\Http\ClientInterface;

/**
 * Non mandatory interface to define base of a endpoint service to execute a HTTP request and send to the
 * client the result of this operation.
 *
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface EndPointInterface
{
    /**
     * To ask the client to redirect to another request.
     *
     * @param ClientInterface $client
     * @param string          $url    The URL to redirect to
     * @param int             $status The status code to use for the Response
     *
     * @return EndPointInterface
     */
    public function redirect(ClientInterface $client, string $url, int $status = 302): EndPointInterface;

    /**
     * Renders a view via a template engine like Twig or another system.
     *
     * @param ClientInterface      $client
     * @param string               $view       The view name
     * @param array<string, mixed> $parameters An array of parameters to pass to the view
     * @param int                  $status The status code to use for the Response
     *
     * @return EndPointInterface
     */
    public function render(
        ClientInterface $client,
        string $view,
        array $parameters = array(),
        int $status = 200
    ): EndPointInterface;
}
