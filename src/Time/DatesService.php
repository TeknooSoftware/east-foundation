<?php

/*
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

namespace Teknoo\East\Foundation\Time;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Clock\ClockInterface;

/**
 * Simple service to manage date and hour in a recipe to return always the same date during the request and avoid
 * differences between two datetime instance.
 *
 * You can override the date to pass by calling "setCurrentDate"
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class DatesService implements ClockInterface
{
    private ?DateTimeInterface $currentDate = null;

    public function setCurrentDate(DateTimeInterface|ClockInterface $currentDate): DatesService
    {
        if ($currentDate instanceof ClockInterface) {
            $this->currentDate = $currentDate->now();
        } else {
            $this->currentDate = $currentDate;
        }

        return $this;
    }

    private function getCurrentDate(): DateTimeInterface
    {
        if ($this->currentDate instanceof DateTimeInterface) {
            return $this->currentDate;
        }

        return $this->currentDate = new DateTime();
    }

    public function passMeTheDate(callable $setter, bool $preferRealDate = false): self
    {
        if (false === $preferRealDate) {
            $setter(clone $this->getCurrentDate());
        } else {
            $setter($this->currentDate = new DateTime());
        }

        return $this;
    }

    private function modify(string $period, callable $setter, bool $preferRealDate = false): self
    {
        $this->passMeTheDate(
            setter: static function (DateTimeInterface $dateTime) use ($setter, $period): void {
                $dateTime = DateTime::createFromInterface($dateTime);
                $setter($dateTime->modify($period));
            },
            preferRealDate: $preferRealDate,
        );

        return $this;
    }

    public function since(string $period, callable $setter, bool $preferRealDate = false): self
    {
        $this->modify("-$period", $setter, $preferRealDate);

        return $this;
    }

    public function forward(string $period, callable $setter, bool $preferRealDate = false): self
    {
        $this->modify("+$period", $setter, $preferRealDate);

        return $this;
    }

    public function now(): DateTimeImmutable
    {
        $now = $this->getCurrentDate();
        if ($now instanceof DateTimeImmutable) {
            return $now;
        }

        return DateTimeImmutable::createFromInterface($now);
    }
}
