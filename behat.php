<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\PHPUnitAssertionsExtension\BehatPHPUnitAssertionsExtension;
use Behat\PHPUnitAssertionsExtension\PHPUnitExceptionStringer;
use Teknoo\Tests\East\Foundation\Behat\FeatureContext;

// behat/phpunit-assertions-extension 1.0.0 imports a class removed in Behat 4.0 : to remove when fixed upstream
class_alias(PHPUnitExceptionStringer::class, 'Behat\Testwork\Exception\Stringer\PHPUnitExceptionStringer');

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withExtension(new Extension(BehatPHPUnitAssertionsExtension::class))
            ->withSuite(
                (new Suite('default'))
                    ->withContexts(FeatureContext::class)
            )
    );
