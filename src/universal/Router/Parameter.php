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
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Foundation\Router;

use Teknoo\Immutable\ImmutableTrait;

/**
 * ValueObject representing a parameter for a controller.
 */
class Parameter implements ParameterInterface
{
    use ImmutableTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $hasDefaultValue;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @var \ReflectionClass|null
     */
    private $classHinted;

    /**
     * Parameter constructor.
     *
     * @param string $name
     * @param bool   $hasDefaultValue
     * @param mixed  $defaultValue
     * @param $classHinted
     *
     * @throws \InvalidArgumentException when $classHinted is invalid (not a \ReflectionClass or null value
     */
    public function __construct(string $name, bool $hasDefaultValue, $defaultValue, $classHinted)
    {
        $this->uniqueConstructorCheck();

        if (null !== $classHinted && !$classHinted instanceof \ReflectionClass) {
            throw new \InvalidArgumentException('$classHinted need null or \ReflectionClass instance');
        }

        $this->name = $name;
        $this->hasDefaultValue = $hasDefaultValue;
        $this->defaultValue = $defaultValue;
        $this->classHinted = $classHinted;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * {@inheritdoc}
     */
    public function hasClass(): bool
    {
        return $this->classHinted instanceof \ReflectionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass(): \ReflectionClass
    {
        return $this->classHinted;
    }
}
