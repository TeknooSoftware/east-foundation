<?php

namespace Teknoo\Tests\East\Framework;

use Teknoo\East\Framework\EastFrameworkBundle;

/**
 * Class EastFrameworkBundleTest
 * @package Teknoo\Tests\East\Framework
 * @covers Teknoo\East\Framework\EastFrameworkBundle
 */
class EastFrameworkBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return EastFrameworkBundle
     */
    private function buildBundle(): EastFrameworkBundle
    {
        return new EastFrameworkBundle();
    }
}