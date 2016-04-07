<?php

namespace Teknoo\Tests\East\Framework\Manager;

use Teknoo\East\Framework\Manager\Manager;

/**
 * Class ManagerTest
 * @package Teknoo\Tests\East\Framework\Manager
 * @covers Teknoo\East\Framework\Manager\Manager
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Manager
     */
    private function buildManager(): Manager
    {
        return new Manager();
    }
}