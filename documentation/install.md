Teknoo Software - East Foundation - Installation
================================================

With Symfony Recipes
--------------------

If you use Symfony and Symfony recipes :

    composer require teknoo/east-foundation-symfony  

And this library is now fully installed. Have a good day

For Symfonym without Symfony Recipes
------------------------------------

Run

    composer require teknoo/east-foundation-symfony  

Or run

    composer require teknoo/bridge-phpdi-symfony
    composer require teknoo/east-foundation

Add to your `bundles.php` file, for all environments these bundles :

* `Teknoo\DI\SymfonyBridge\DIBridgeBundle`
* `Teknoo\East\FoundationBundle\EastFoundationBundle`

Create the file `config/packages/east_foundation.yaml` with this content :

    di_bridge:
        definitions:
            - '{ priority: 30, file: %kernel.project_dir%/vendor/teknoo/east-foundation/src/di.php' }
            - '{ priority: 30, %kernel.project_dir%/vendor/teknoo/east-foundation/infrastructures/symfony/Resources/config/di.php' }
            - '{ priority: 30, %kernel.project_dir%/vendor/teknoo/east-foundation/infrastructures/symfony/Resources/config/laminas_di.php' }
        import:
            Psr\\Log\\LoggerInterface: 'logger'

Other framework
---------------
Currently, there are not other implementation for other framework. Please contact us :).
