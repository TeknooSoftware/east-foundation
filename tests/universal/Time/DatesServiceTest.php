<?php

/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Foundation\Time;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Teknoo\East\Foundation\Time\DatesService;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(DatesService::class)]
class DatesServiceTest extends TestCase
{
    public function buildService(): \Teknoo\East\Foundation\Time\DatesService
    {
        return new DatesService();
    }

    public function testPassMeTheDateWithNoDefinedDate(): void
    {
        $object = new class () {
            private ?\DateTimeInterface $date = null;

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
        $this->assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate($object->setDate(...))
        );

        $this->assertInstanceOf(DateTimeInterface::class, $object->getDate());
        $oldDate = $object->getDate();

        $service->passMeTheDate($object->setDate(...));
        $this->assertEquals($oldDate, $object->getDate());
    }

    public function testPassMeTheDateWithDefinedDate(): void
    {
        $date = new DateTime('2017-01-01');

        $object = new class () {
            private ?\DateTimeInterface $date = null;

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

        $this->assertInstanceOf(
            DatesService::class,
            $service->setCurrentDate($date)
        );

        $this->assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate($object->setDate(...))
        );

        $this->assertEquals($date, $object->getDate());
    }

    public function testPassMeTheDateWithDefinedDateFromClockk(): void
    {
        $date = new DateTime('2017-01-01');

        $object = new class () {
            private ?\DateTimeInterface $date = null;

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

        $clock = $this->createStub(ClockInterface::class);
        $clock
            ->method('now')
            ->willReturn(DateTimeImmutable::createFromInterface($date));

        $this->assertInstanceOf(
            DatesService::class,
            $service->setCurrentDate($clock)
        );

        $this->assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate($object->setDate(...))
        );

        $this->assertEquals($date, $object->getDate());
    }

    public function testPassMeTheDateWithRealDate(): void
    {
        $date = new DateTime('2017-01-01');

        $object = new class () {
            private ?\DateTimeInterface $date = null;

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

        $this->assertInstanceOf(
            DatesService::class,
            $service->setCurrentDate($date)
        );

        $this->assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate($object->setDate(...), true)
        );

        $this->assertNotEquals($date, $object->getDate());
    }

    public function testPassMeTheDateWithRealDateAndRefresInternalDate(): void
    {
        $date = new DateTime('2017-01-01');

        $object = new class () {
            private ?\DateTimeInterface $date = null;

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

        $this->assertInstanceOf(
            DatesService::class,
            $service->setCurrentDate($date)
        );

        $object2 = clone $object;
        $this->assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate($object->setDate(...), true)
        );

        $this->assertNotEquals($date, $object->getDate());

        $this->assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate($object2->setDate(...))
        );

        $this->assertNotEquals($date, $object2->getDate());
        $this->assertNotSame($object->getDate(), $object2->getDate());
        $this->assertEquals($object->getDate(), $object2->getDate());
    }

    public function testSince(): void
    {
        $object = new class () {
            private ?\DateTimeInterface $date = null;

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
        $this->assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate($object->setDate(...))
        );

        $date = new DateTime('2017-01-06');
        $this->assertInstanceOf(
            DatesService::class,
            $service->setCurrentDate($date)
        );

        $service->since('5 days', $object->setDate(...));
        $this->assertEquals(new DateTime('2017-01-01'), $object->getDate());
    }

    public function testForward(): void
    {
        $object = new class () {
            private ?\DateTimeInterface $date = null;

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
        $this->assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate($object->setDate(...))
        );

        $date = new DateTime('2017-01-06');
        $this->assertInstanceOf(
            DatesService::class,
            $service->setCurrentDate($date)
        );

        $service->forward('5 days', $object->setDate(...));
        $this->assertEquals(new DateTime('2017-01-11'), $object->getDate());
    }

    public function testSinceWithRealDate(): void
    {

        $object = new class () {
            private ?\DateTimeInterface $date = null;

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

        $this->assertInstanceOf(
            DatesService::class,
            $service->since('5 days', $object->setDate(...), true)
        );
        $this->assertInstanceOf(DateTimeInterface::class, $object->getDate());
    }

    public function testForwardWithRealDate(): void
    {
        $object = new class () {
            private ?\DateTimeInterface $date = null;

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

        $this->assertInstanceOf(
            DatesService::class,
            $service->forward('5 days', $object->setDate(...), true)
        );
        $this->assertInstanceOf(DateTimeInterface::class, $object->getDate());
    }

    public function testNowWithNoDefinedDate(): void
    {
        $service = $this->buildService();
        $this->assertInstanceOf(
            DateTimeImmutable::class,
            $service->now(),
        );
    }

    public function testNowWithDefinedDate(): void
    {
        $date = new DateTime('2017-01-01');

        $service = $this->buildService();

        $this->assertInstanceOf(
            DatesService::class,
            $service->setCurrentDate($date)
        );

        $this->assertInstanceOf(
            DateTimeImmutable::class,
            $now = $service->now(),
        );

        $this->assertEquals($date, $now);
    }

    public function testNowWithDefinedImmutableDate(): void
    {
        $date = new DateTimeImmutable('2017-01-01');

        $service = $this->buildService();

        $this->assertInstanceOf(
            DatesService::class,
            $service->setCurrentDate($date)
        );

        $this->assertInstanceOf(
            DateTimeImmutable::class,
            $now = $service->now(),
        );

        $this->assertEquals($date, $now);
    }
}
