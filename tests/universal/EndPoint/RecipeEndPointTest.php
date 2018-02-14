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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Foundation\EndPoint;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\EndPoint\RecipeEndPoint;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\Recipe\ChefInterface;

/**
 * Class RecipeEndPointTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Foundation\EndPoint\RecipeEndPoint
 */
class RecipeEndPointTest extends TestCase
{
    /**
     * @var ChefInterface
     */
    private $chef;

    /**
     * @return ChefInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getChefMock(): ChefInterface
    {
        if (!$this->chef instanceof ChefInterface) {
            $this->chef = $this->createMock(ChefInterface::class);
        }

        return $this->chef;
    }

    /**
     * @return RecipeEndPoint
     */
    public function buildEndPoint(): RecipeEndPoint
    {
        return new RecipeEndPoint($this->getChefMock());
    }

    /**
     * @expectedException \TypeError
     */
    public function testInvokeBadRequest()
    {
        $endPoint = $this->buildEndPoint();
        $endPoint(new \stdClass(), $this->createMock(ClientInterface::class));
    }

    /**
     * @expectedException \TypeError
     */
    public function testInvokeBadClient()
    {
        $endPoint = $this->buildEndPoint();
        $endPoint($this->createMock(ServerRequestInterface::class), new \stdClass());
    }

    public function testInvoke()
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn(['bar' => 456]);
        $requestMock->expects(self::any())->method('getParsedBody')->willReturn(['foo' => 123, 'request' => 'bar']);
        $requestMock->expects(self::any())->method('getQueryParams')->willReturn(['bar' => 123, 'client' => 'foo']);

        $clientMock = $this->createMock(ClientInterface::class);

        $this->getChefMock()
            ->expects(self::once())
            ->method('process')
            ->with([
                'bar' => 456,
                'foo' => 123,
                'request' => $requestMock,
                'client' => $clientMock,
            ])
            ->willReturnSelf();

        $endPoint = $this->buildEndPoint();

        self::assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint($requestMock, $clientMock)
        );
    }
}
