<?php
/**
 * East Framework.
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

namespace Teknoo\East\Framework\Processor;

use Teknoo\East\Framework\Http\ClientInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface ProcessorInterface is a contract to create processor to instantiate controller action and pass the request.
 *
 * All public method of the manager must only return the self client or a clone instance.
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface ProcessorInterface
{
    /**
     * Called by a router to execute a request and passing a the client
     *
     * @param ClientInterface $client
     * @param ServerRequestInterface $request
     * @param array $requestParameters
     * @return ProcessorInterface
     */
    public function executeRequest(
        ClientInterface $client,
        ServerRequestInterface $request,
        array $requestParameters
    ): ProcessorInterface;
}
