<?php

namespace Teknoo\Tests\East\Framework\DependencyInjection;

use Teknoo\East\Framework\DependencyInjection\EastFrameworkCompilerPass;

/**
 * Class EastFrameworkCompilerPassTest
 * @package Teknoo\Tests\East\Framework\DependencyInjection
 * @covers Teknoo\East\Framework\DependencyInjection\EastFrameworkCompilerPass
 */
class EastFrameworkCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return EastFrameworkCompilerPass
     */
    private function buildCompilerPass(): EastFrameworkCompilerPass
    {
        return new EastFrameworkCompilerPass();
    }
}