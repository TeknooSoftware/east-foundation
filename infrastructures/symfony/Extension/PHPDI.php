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
use Teknoo\East\Foundation\Extension\Manager;
use Teknoo\East\Foundation\Extension\ManagerInterface;
use Teknoo\East\Foundation\Extension\ModuleInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PHPDI implements ModuleInterface, ExtensionInterface
{
    private static ?self $instance = null;

    private ?BridgeBuilderInterface $builder;

    public function __construct(
        private ?ManagerInterface $manager = null
    ) {
        if (!$this->manager) {
            $this->manager = Manager::run();
        }
    }

    public static function create(): ExtensionInterface
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function configure(BridgeBuilderInterface $builder): ExtensionInterface
    {
        if (!$this->manager) {
            throw new \RuntimeException('todo');
        }

        $that = clone $this;
        $that->builder = $builder;

        $this->manager->execute($that);

        return $this;
    }

    public function prepareCompilation(?string $compilationPath): self
    {
        if (!$this->builder) {
            throw new \RuntimeException('todo');
        }

        $this->builder->prepareCompilation($compilationPath);

        return $this;
    }

    public function enableCache(bool $enable): self
    {
        if (!$this->builder) {
            throw new \RuntimeException('todo');
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
            throw new \RuntimeException('todo');
        }

        $this->builder->loadDefinition($definitions);

        return $this;
    }

    public function import(string $diKey, string $sfKey): self
    {
        if (!$this->builder) {
            throw new \RuntimeException('todo');
        }

        $this->builder->import($diKey, $sfKey);

        return $this;
    }
}
