<?php

namespace Teknoo\Tests\East\Framework\DependencyInjection;

use Teknoo\East\Framework\DependencyInjection\Configuration;

/**
 * Class ConfigurationTest
 * @package Teknoo\Tests\East\Framework\DependencyInjection
 * @covers Teknoo\East\Framework\DependencyInjection\Configuration
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Configuration
     */
    private function buildConfiguration(): Configuration
    {
        return new Configuration();
    }
}
