<?php

namespace Teknoo\Tests\East\Framework\DependencyInjection;

use Teknoo\East\Framework\DependencyInjection\EastFrameworkExtension;

/**
 * Class EastFrameworkExtensionTest
 * @package Teknoo\Tests\East\Framework\DependencyInjection
 * @covers Teknoo\East\Framework\DependencyInjection\EastFrameworkExtension
 */
class EastFrameworkExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return EastFrameworkExtension
     */
    private function buildExtension(): EastFrameworkExtension
    {
        return new EastFrameworkExtension();
    }
}
