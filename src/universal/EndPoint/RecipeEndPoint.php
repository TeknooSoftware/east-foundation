<?php

declare(strict_types=1);

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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Foundation\EndPoint;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\Recipe\ChefInterface;

/**
 * EndPoint wrapper to execute a recipe as endpoint The workplan is build with the server request and the client
 * instance (with keys "request" and "client").
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RecipeEndPoint
{
    /**
     * @var ChefInterface
     */
    private $chef;

    /**
     * RecipeEndPoint constructor.
     * @param ChefInterface $chef
     */
    public function __construct(ChefInterface $chef)
    {
        $this->chef = $chef;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClientInterface $client
     * @return RecipeEndPoint
     */
    public function __invoke(ServerRequestInterface $request, ClientInterface $client): RecipeEndPoint
    {
        $this->chef->process([
            'client' => $client,
            'request' => $request
        ]);

        return $this;
    }
}
