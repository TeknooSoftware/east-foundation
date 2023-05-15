<?php
/**
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
defined('RUN_CLI_MODE')
    || define('RUN_CLI_MODE', true);

defined('PHPUNIT')
    || define('PHPUNIT', true);

ini_set('memory_limit', '64M');

require_once __DIR__.'/../vendor/autoload.php';

date_default_timezone_set('UTC');

error_reporting(E_ALL | E_STRICT);

if (!\function_exists('pcntl_async_signals')) {
    define('PCNTL_MOCKED', true);

    function pcntl_async_signals(bool $enable)
    {
    }
}

if (!\function_exists('pcntl_signal')) {
    function pcntl_signal(int $signal, callable $callback)
    {
    }
}

if (!\function_exists('pcntl_alarm')) {
    function pcntl_alarm(int $seconds) {

    }
}