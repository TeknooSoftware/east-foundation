<?php

namespace Teknoo\Tests\East\Framework\Controller;

use Teknoo\East\Framework\Controller\Controller;

/**
 * Class ControllerTest
 * @package Teknoo\Tests\East\Framework\Controller
 * @covers Teknoo\East\Framework\Controller\Controller
 */
class ControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Controller
     */
    private function buildController(): Controller
    {
        return new Controller();
    }
}