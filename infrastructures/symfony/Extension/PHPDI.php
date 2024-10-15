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

namespace Teknoo\East\FoundationBundle\Extension;

use Teknoo\DI\SymfonyBridge\Container\BridgeBuilderInterface;
use Teknoo\DI\SymfonyBridge\Extension\ExtensionInterface;
use Teknoo\East\Foundation\Extension\ExtensionInitTrait;
use Teknoo\East\Foundation\Extension\Manager;
use Teknoo\East\Foundation\Extension\ManagerInterface;
use Teknoo\East\Foundation\Extension\ModuleInterface;
use Teknoo\East\FoundationBundle\Extension\Exception\MissingBuilderException;

/**
 * Extension module to extend PHPDI configuration in an extension
 * This module is build on Teknoo\DI\SymfonyBridge\Extension\ExtensionInterface and can be defined as PHPDI extension
 * in the symfony configuration. THe PHP-DI Bridge will automatically call the method configure.
 * This Module will forward the call to exttensions through the East Extension Manager
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @phpstan-consistent-constructor
 */
class PHPDI implements ModuleInterface, ExtensionInterface
{
    private ?BridgeBuilderInterface $builder = null;

    private ManagerInterface $manager;

    private static ?self $instance = null;

    public function __construct(
        ?ManagerInterface $manager = null
    ) {
        $this->manager = $manager ?? Manager::run();
    }

    public static function create(?ManagerInterface $manager = null): self
    {
        if (null === self::$instance) {
            self::$instance = new static($manager);
        }

        return self::$instance;
    }

    public function configure(BridgeBuilderInterface $builder): ExtensionInterface
    {
        $that = clone $this;
        $that->builder = $builder;

        $this->manager->execute($that);

        return $this;
    }

    public function prepareCompilation(?string $compilationPath): self
    {
        if (!$this->builder) {
            throw new MissingBuilderException('The PHPDI Container builder is not defined in this module instance');
        }

        $this->builder->prepareCompilation($compilationPath);

        return $this;
    }

    public function enableCache(bool $enable): self
    {
        if (!$this->builder) {
            throw new MissingBuilderException('The PHPDI Container builder is not defined in this module instance');
        }

        $this->builder->enableCache($enable);

        return $this;
    }

    /**
     * @param array<int, array{priority?:int, file:string}> $definitions
     */
    public function loadDefinition(array $definitions): self
    {
        if (!$this->builder) {
            throw new MissingBuilderException('The PHPDI Container builder is not defined in this module instance');
        }

        $this->builder->loadDefinition($definitions);

        return $this;
    }

    public function import(string $diKey, string $sfKey): self
    {
        if (!$this->builder) {
            throw new MissingBuilderException('The PHPDI Container builder is not defined in this module instance');
        }

        $this->builder->import($diKey, $sfKey);

        return $this;
    }
}
