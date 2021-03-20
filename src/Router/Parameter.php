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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Router;

use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;
use Teknoo\Immutable\ImmutableTrait;

/**
 * ValueObject representing a parameter for a controller.
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Parameter implements ParameterInterface
{
    use ImmutableTrait;

    private string $name;

    private bool $hasDefaultValue;

    private mixed $defaultValue;

    private ?ReflectionClass $classHinted;

    /**
     * @throws InvalidArgumentException when $classHinted is invalid (not a \ReflectionClass or null value
     */
    public function __construct(string $name, bool $hasDefaultValue, mixed $defaultValue, ?ReflectionClass $classHinted)
    {
        $this->uniqueConstructorCheck();

        $this->name = $name;
        $this->hasDefaultValue = $hasDefaultValue;
        $this->defaultValue = $defaultValue;
        $this->classHinted = $classHinted;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function hasClass(): bool
    {
        return $this->classHinted instanceof ReflectionClass;
    }

    public function getClass(): ReflectionClass
    {
        if (!$this->classHinted instanceof ReflectionClass) {
            throw new RuntimeException('Error this parameter ' . $this->name . ' has not class hinted');
        }

        return $this->classHinted;
    }
}
