<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
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

namespace Teknoo\Tests\East\Foundation\Time;

use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Time\DatesService;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Foundation\Time\DatesService
 */
class DatesServiceTest extends TestCase
{
    public function buildService()
    {
        return new DatesService();
    }

    public function testPassMeTheDateWithNoDefinedDate()
    {
        $object = new class {
            private $date;
            public function getDate(): ?DateTimeInterface
            {
                return $this->date;
            }
            public function setDate(DateTimeInterface $date): self
            {
                $this->date = $date;

                return $this;
            }
        };

        $service = $this->buildService();
        self::assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate($object->setDate(...))
        );

        self::assertInstanceOf(DateTimeInterface::class, $object->getDate());
        $oldDate = $object->getDate();

        $service->passMeTheDate($object->setDate(...));
        self::assertEquals($oldDate, $object->getDate());
    }

    public function testPassMeTheDateWithDefinedDate()
    {
        $date = new DateTime('2017-01-01');

        $object = new class {
            private $date;
            public function getDate(): ?DateTimeInterface
            {
                return $this->date;
            }
            public function setDate(DateTimeInterface $date): self
            {
                $this->date = $date;
                return $this;
            }
        };

        $service = $this->buildService();

        self::assertInstanceOf(
            DatesService::class,
            $service->setCurrentDate($date)
        );

        self::assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate($object->setDate(...))
        );

        self::assertEquals($date, $object->getDate());
    }

    public function testPassMeTheDateWithRealDate()
    {
        $date = new DateTime('2017-01-01');

        $object = new class {
            private $date;
            public function getDate(): ?DateTimeInterface
            {
                return $this->date;
            }
            public function setDate(DateTimeInterface $date): self
            {
                $this->date = $date;
                return $this;
            }
        };

        $service = $this->buildService();

        self::assertInstanceOf(
            DatesService::class,
            $service->setCurrentDate($date)
        );

        self::assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate($object->setDate(...), true)
        );

        self::assertNotEquals($date, $object->getDate());
    }

    public function testPassMeTheDateWithRealDateAndRefresInternalDate()
    {
        $date = new DateTime('2017-01-01');

        $object = new class {
            private $date;
            public function getDate(): ?DateTimeInterface
            {
                return $this->date;
            }
            public function setDate(DateTimeInterface $date): self
            {
                $this->date = $date;
                return $this;
            }
        };

        $service = $this->buildService();

        self::assertInstanceOf(
            DatesService::class,
            $service->setCurrentDate($date)
        );

        $object2 = clone $object;
        self::assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate($object->setDate(...), true)
        );

        self::assertNotEquals($date, $object->getDate());

        self::assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate([$object2, 'setDate'])
        );

        self::assertNotEquals($date, $object2->getDate());
        self::assertNotSame($object->getDate(), $object2->getDate());
        self::assertEquals($object->getDate(), $object2->getDate());
    }

    public function testSince()
    {
        $object = new class {
            private $date;
            public function getDate(): ?DateTimeInterface
            {
                return $this->date;
            }
            public function setDate(DateTimeInterface $date): self
            {
                $this->date = $date;

                return $this;
            }
        };

        $service = $this->buildService();
        self::assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate($object->setDate(...))
        );

        $date = new DateTime('2017-01-06');
        self::assertInstanceOf(
            DatesService::class,
            $service->setCurrentDate($date)
        );

        $service->since('5 days', $object->setDate(...));
        self::assertEquals(new DateTime('2017-01-01'), $object->getDate());
    }

    public function testSinceWithRealDate()
    {

        $object = new class {
            private $date;
            public function getDate(): ?DateTimeInterface
            {
                return $this->date;
            }
            public function setDate(DateTimeInterface $date): self
            {
                $this->date = $date;
                return $this;
            }
        };

        $service = $this->buildService();

        self::assertInstanceOf(
            DatesService::class,
            $service->since('5 days', $object->setDate(...), true)
        );
        self::assertInstanceOf(DateTimeInterface::class, $object->getDate());
    }
}
