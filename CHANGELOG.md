# Teknoo Software - East Foundation - Change Log

## [8.0.5] - 2025-06-17
### Stable Release
- Fix issue with last phpunit updates.

## [8.0.4] - 2025-02-07
### Stable Release
- Update dev lib requirements
    - Require Symfony libraries 6.4 or 7.2
    - Update to PHPUnit 12
- Drop support of PHP 8.2
    - The library stay usable with PHP 8.2, without any waranties and tests
    - In the next major release, Support of PHP 8.2 will be dropped

## [8.0.3] - 2024-11-25
### Stable Release
- Fix issue with PHP8.4 and compiled container

## [8.0.2] - 2024-11-25
### Stable Release
- Fix deprecation into test with PHP 8.4

## [8.0.1] - 2024-11-20
### Stable Release
- `Teknoo\East\FoundationBundle\Command\Client` use constant for `returnCode` following POSIX return code.

## [8.0.0] - 2024-11-01
### Stable Release
- Migrate to `Teknoo Recipe` 6.
- Rename `Cookbook` to `Plan`.
  - Old classes and interfaces are deprecated.
- Migrate to `EditablePlan` all previous `Cookbook` / `Plan`.
- Remove `registerMiddleware` in East Foundation Recipe.
  - Remove `Recipe::registerMiddleware`.
  - Replaced by usind directly cook or add method in the dedicated plan or recipe.
- Migrate the decoration about the East Foundation Plan to register the `SessionMiddleware`.

## [7.9.0] - 2024-10-28
### Stable Release
- Add `ManagerInterface::listLoadedExtensions` to get the list of loaded extension
- Add `__toString` method to `ExtensionInterface` to get an human-readable.
- Set `Manager::instance` as protected to allow replacement by manager's inherited classes.

## [7.8.3] - 2024-10-15
### Stable Release
- Pass also app env to Routes extension module

## [7.8.2] - 2024-10-15
### Stable Release
- Complete services.yaml to create from container PHPDI module, but, by default, it's not used, because the container is
  not ready when the PHP DI Bridge extension call it. The Symfony container is configured to call module's factory to
  retrieve the singleton

## [7.8.1] - 2024-10-14
### Stable Release
- Update requirements libs
- Use `random_bytes` instead of `uniqid`

## [7.8.0] - 2024-10-11
### Stable Release
- New feature to provide an extension behavior in your application, and symfony app to extend
  them without update configuration of your application. Ideal to distribute your applications with
  plugin or a marketplace.
  There are three parts :
    - Modules contracted thanks to `Teknoo\East\Foundation\Extension\ModuleInterface`. There are no methods defined,
      each module has its own behavior
    - `Teknoo\East\Foundation\Extension\ExtensionInterface` to implement in your extension (or plugin) to update the
      application configuration according to modules passed
    - An extension manager, contracted thanks to `Teknoo\East\Foundation\Extension\ManagerInterface` and
      implemented by `Teknoo\East\Foundation\Extension\Manager`, to find and load extensions when a module is called.
    - When the extension is called, it can ignore or update the configuration through the module passed.
    - This feature does not use a DI, because theses operations are often performed during the initialization, so
      singleton and static methods are used.
    - Manager use loader, contracted by `Teknoo\East\Foundation\Extension\LoaderInterface` to detect and load extensions.
      There are two embedded loader:
        - `Teknoo\East\Foundation\Extension\FileLoader` to load all extensions declared into a json array in a file,
          by default `extensions/enabled.json`, but it can be overrided by setting the env var
          `TEKNOO_EAST_EXTENSION_FILE`.
        - `Teknoo\East\Foundation\Extension\ComposerLoader` to load all extensions implementing the interface from the
        classmap of Composer.
        - By default, `FileLoader` is used but, the loader can be changed thanks to the env var
          `TEKNOO_EAST_EXTENSION_LOADER` (need the full qualified classname of the desired loader).
    - To disable this behavior, evan if module are loaded, you can set the env var `TEKNOO_EAST_EXTENSION_DISABLED`
      (any value, must be only set and non empty)
    - Three modules are provided :
        - `Teknoo\East\FoundationBundle\Extension\Bundles` to use in your `bundles.php` file to extend the list of
           Symfony Bundle to load.
        - `Teknoo\East\FoundationBundle\Extension\Routes` to use in your `src/Kernel.php` file to complete the list of
           routes.
        - `Teknoo\East\FoundationBundle\Extension\PHPDI`, to extend the configuration of PHP-DI when the
           `Teknoo PHP DI Bridge` is used. To enable it, add to your Symfony configuration :

                   di_bridge:
                       extensions:
                           - { name: 'Teknoo\East\FoundationBundle\Extension\PHPDI', priority: 0 }

- Fix some PSR4
- Remove Composer unused in dev requirements
- Update to PHPUnit 11 and devs libraries
- Enable PHP8.4 in tests and fix deprecations

## [7.7.1] - 2024-05-31
### Stable Release
- Fix deprecated : replace `Symfony\Component\HttpKernel\DependencyInjection\Extension`
        by `Symfony\Component\DependencyInjection\Extension\Extension`

## [7.7.0] - 2024-05-07
### Stable Release
- Drop support of PHP 8.1
- Add sensitive parameter attribute on methods catching throwable to prevent leak.

## [7.6.4] - 2024-05-05
### Stable Release
- Add some resetable behavior on some symfony services.

## [7.6.3] - 2024-03-22
### Stable Release
- Fix support of last PHPStan 1.10.64
- Use State 6.2

## [7.6.2] - 2024-03-13
### Stable Release
- Use Recipe 5+
- Improve SleepService to use new `Promise` feature

## [7.6.1] - 2024-02-13
### Stable Release
- Prevent bug with `TimerService` and `SleepService` if `$seconds` is `0`

## [7.6.0] - 2024-02-11
### Stable Release
- GroupsTrait support lazy data to fetch collections only after filtering key. 
  - Lazy Data must be passed in a closure called by `GroupsTrait::filterExport` if the arg `$lazyData` is at `true`.

## [7.5.2] - 2024-02-04
### Stable Release
- Update documentation.
- Require `Recipe` 4.6+ 

## [7.5.1] - 2024-01-31
### Stable Release
- `TimerService` clone the `DatesService` instance to avoid collusion when it use the real date. 

## [7.5.0] - 2024-01-25
### Stable Release
- Add `DatesService::forward` to get a date in the futur. Same behavior of `DateTime::modify`

## [7.4.0] - 2023-12-01
### Stable Release
- Add a pseudo non blocking Sleep service, build on timer

## [7.3.6] - 2023-12-01
### Stable Release
- Update dev lib requirements
- Support Symfony 7+

## [7.3.5] - 2023-11-30
### Stable Release
- Update dev lib requirements
- Support Symfony 7+

## [7.3.4] - 2023-11-29
### Stable Release
- Update dev lib requirements
- Support Symfony 7+

## [7.3.3] - 2023-11-09
### Stable Release
- Use last Recipe version

## [7.3.2] - 2023-11-08
### Stable Release
- Add `#[SensitiveParameter]` to normalizer to prevent leaks
- Update composer audit configuration
- Fix `Teknoo\East\Foundation\Command\Executor` DI definition with a dedicated manager

## [7.3.1] - 2023-08-21
### Stable Release
- `SessionMiddleware` does not get Session object if the request as the attribute `_stateless` to avoid
  session creation.

## [7.3.0] - 2023-08-10
### Stable Release
- Add `Teknoo\East\Foundation\Normalizer\Object\GroupsTrait` to support groups serializations / normalizations.
- Fix doctype in `EastNormalizer`.

## [7.2.0] - 2023-07-25
### Stable Release
- Support `PSR-15` with add to new `Recipe Bowl` type :
  - `FiberHandlerBowl` (and `FiberMiddlewareBowl`) to support PSR 15 Requests handlers into a recipe.
  - `MiddlewareBowl` (and `FiberMiddlewareBowl`) to support PSR 15 middleware into a recipe.
    - The injected request handler is a wrap of the East Manager to resume/continue the recipe and get a PSR Response
      from the client.
  - These bowls will automatically push response to the client at the end of the execution

## [7.1.0] - 2023-07-20
### Stable Release
- Support `PSR-20` with the `DatesService`.

## [7.0.0] - 2023-07-13
### Stable Release
- Support PHP-DI 7.0+
- Support Laminas Diactoros 3.0+

## [7.0.0-beta1] - 2023-07-12
### Beta Release
- Support PHP-DI 7.0+
- Support Laminas Diactoros 3.0+

## [6.2.12] - 2023-06-25
### Stable Release
- Fix deprecation in Symfony normalizer

## [6.2.11] - 2023-06-07
### Stable Release
- Update Teknoo libs
- Require Symfony 6.3 or newer
- Improve documentation

## [6.2.10] - 2023-05-15
### Stable Release
- Update dev lib requirements
- Update copyrights

## [6.2.9] - 2023-04-16
### Stable Release
- Update dev lib requirements
- Support PHPUnit 10.1+
- Migrate phpunit.xml

## [6.2.8] - 2023-04-11
### Stable Release
- Allow psr/http-message 2

## [6.2.7] - 2023-03-11
### Stable Release
- Q/A

## [6.2.6] - 2023-02-12
### Stable Release
- Add `Teknoo\East\Foundation\Liveness\PingServiceInterface` for `PingService`
- Add `Teknoo\East\Foundation\Liveness\TimeoutServiceInterface` for `TimeoutService`
- Add `Teknoo\East\Foundation\Time\TimerServiceInterface` for `TimerService`

## [6.2.5] - 2023-02-11
### Stable Release
- Remove phpcpd and upgrade phpunit.xml

## [6.2.4] - 2023-02-03
### Stable Release
- Update dev libs to support PHPUnit 10 and remove unused phploc

## [6.2.3] - 2023-02-01
### Stable Release
- Fix `TimerService` when a timer is rescheduled without unregister before. 

## [6.2.2] - 2023-01-26
### Stable Release
- Fix `TimerService` when a new timer with a previous used id, to not be called at previous time.

## [6.2.1] - 2023-01-24
### Stable Release
- Fix `TimerService` to allow reschedule a new timer with the same timer id during the timer call.

## [6.2.0] - 2023-01-22
### Stable Release
- Add `DatesService` from Teknoo East Common
- Add `DatesService::since` to get a date in the past. Same behavior of `DateTime::modify`
- Add `TimerService` to schedule a task / function call in x seconds, several tasks in different timer can be scheduled
- Add `TimeoutService` to centralize and manager `set_time_limit` behavior and use a throwable exception instead of 
  fatal error
- Add a `PingService` to centralize ping behavior for liveness feature.
- Migrate `Executor` from Symfony Messenger to `East\Foundation\Command`

## [6.1.0] - 2022-11-15
### Stable Release
- Some QA fixes
- Remove support of Symfony 6.0

## [6.0.7] - 2022-08-06
### Stable Release
- Update composer.json

## [6.0.6] - 2022-06-29
### Stable Release
- Fix deprecated in `EastNormalizer`

## [6.0.5] - 2022-06-17
### Stable Release
- Clean code and test thanks to Rector
- Update libs requirements

## [6.0.4] - 2022-04-17
### Stable Release
- Rename `.yml` files to `.yaml`

## [6.0.3] - 2022-03-08
### Stable Release
- Require Recipe 4.1.2 or later
- Improve PHPDoc about `PromiseInterface` with template
- Remove deprecated features from 5.* and below

## [6.0.2] - 2022-03-08
### Stable Release
- Require Immutable 3.0.1 or later

## [6.0.1] - 2022-02-27
### Stable Release
- Requipe Recipe 6.0.1+ to fix issue with `RecipeBowl`.

## [6.0.0] - 2022-02-23
### Stable Release
- Constants are final in `SessionMiddleware`, processors, routers and sessions components
- Use readonly for immutables objects (`Processor` and routers' results)
- Support Fibers in main `Cookbook` and `RecipeEndPoint`. `RecipeEndPoint` accepts also bowl
 and `FiberRecipeBowl`.
- Remove support of PHP 8.0 and Symfony 5.4 and below

## [5.3.11] - 2022-02-11
### Stable Release
- Support Immutable 3.0
- Support State 6.0
- Support Recipe 4.0

## [5.3.10] - 2021-12-12
### Stable Release
- Remove unused QA tool
- Remove support of Symfony 5.3
- Support Symfony 5.4 and 6.0+

## [5.3.9] - 2021-12-03
### Stable Release
- Fix some deprecated with PHP 8.1
- Add `excludedPaths` option in Router to not manage some path in this library 
  and fallback directly to Symfony (usefull for profiler or other stuff from Symfony).

## [5.3.8] - 2021-12-03
### Stable Release
- Fix some deprecated with PHP 8.1

## [5.3.7] - 2021-11-16
### Stable Release
- QA

## [5.3.6] - 2021-11-12
### Stable Release
- Migrate to PHPStan 1.1+.

## [5.3.5] - 2021-09-04
### Stable Release
- Remove dead code in Symfony Router adapter.
- Fix error with `JsonSerializable` response's content type in client.
- Put `SessionInterface` instance also into workplan.

## [5.3.4] - 2021-08-19
### Stable Release
- QA, Fix PHPDoc

## [5.3.3] - 2021-08-11
### Stable Release
- Remove support of Symfony 5.2.
- Switch to Recipe 3.2.
- Switch to Recipe's promise instead East' Promise. 
- East' Promise are now deprecated.

## [5.3.2] - 2021-06-28
### Stable Release
- Update documents and dev libs requirements

## [5.3.1] - 2021-06-25
### Stable Release
- Fix Symfony Http Client implementation to not convert to string a response implementing the PSR Response interface
  and East Response interface. (PSR Response is prior).

## [5.3.0] - 2021-06-23
### Stable Release
- Move ClientInterface to `Teknoo\East\Foundation\Client` from `Teknoo\East\Foundation\Http`
- Add `Teknoo\East\Foundation\Client\ResultInterface`
- `ClientInterface` accept also ResultInterface instead PSR's message
- All clients implementations adopts new client interfaces
- Symfony Clients implementations supports `ResultInterface` and `JsonSerializable` responses

## [5.2.0] - 2021-06-18
### Stable Release
- Add ManagerInterface::updateMessage() to update a message from a step without call continueExecution()

## [5.1.3] - 2021-06-17
### Stable Release
- Fix Processor mandatories ingredients to use classname instead of legacy keyword

## [5.1.2] - 2021-05-31
### Stable Release
- Minor version about libs requirements

## [5.1.1] - 2021-05-28
### Stable Release
- Fix Processor to allow silent client when no compatible callable was found

## [5.1.0] - 2021-05-28
### Stable Release
- Add method to configure client's behavior when a it must send a missing response (silently or throw an exception)
    - Add `ClientInterface::mustSendAResponse`
    - Add `ClientInterface::sendAResponseIsOptional`
- Processor will configure in non silent mode if a compatible callable is available and was returned by Router
    - This behavior can be disable by set `teknoo.east.client.must_send_response` to false in DI

## [5.0.3] - 2021-04-28
### Stable Release
- Some optimisations on array functions to limit O(n)

## [5.0.2] - 2021-03-30
### Stable Release
- switch to str_contains
- Messenger's executor use an empty manager and clone it

## [5.0.1] - 2021-03-24
### Stable Release
- Constructor Property Promotion
- Non-capturing catches

## [5.0.0] - 2021-03-20
### Stable Release
- Migrate to PHP 8.0+
- Remove support of Symfony 4.4, only 5.2+
- QA and license file
  
## [4.1.2] - 2021-03-15
### Stable Release
- Create ResponseMessageFactory to return a PSR 11 response (use Laminas implementation) instead of
  basic Message.

## [4.1.1] - 2021-03-13
### Stable Release
- Add MessageFactory definitions into the laminas_di file

## [4.1.0] - 2021-03-11
### Stable Release
- Add PSR11 Message only implementation 
- Add MessageFactory
- Update Client Interface to use MessageInterface instead of RequestInterface
- Add Recipe executor dedicated to Symfony Messenger
- Add Client dedicated to Symfony Messenger
- Remove some public services

## [4.0.3] - 2021-03-09
### Stable Release
- Clean symfony yaml indentations

## [4.0.2] - 2021-03-07
### Stable Release
- Manager update workplan with canonicals classes names about instances of client and message instead 
  use keyword `request` and `client`

## [4.0.1] - 2021-03-05
### Stable Release
- Replace psr/container-implementation to psr/container

## [4.0.0] - 2021-02-25
### Stable Release
- Switch to States 4.1.9 and PHPStan 0.12.79
- Prepare library to be used in non HTTP context  
- Use MessageInterface instead of ServerRequestInterface
- Cookbook and ProcessorCookbook use BaseCookbookTrait

## [3.3.3] - 2021-02-02
### Stable Release
- Add argument to controller of RecipeEndPoint to allow override/complete some entry in the workplan,
 before fetching service from container thanks to Request's attribute, to escape/secure some workplan's
 ingredients from request's parameters (passed by the HTTP client).

## [3.3.2] - 2021-01-25
### Stable Release
- Update ClientInterface to allow errorInRequest to be called silently without throw an excepton
- Add support of a PSR Logger in Symfony Client implementation
- Fix symfony services definitions and twig definition
- Migrate Client definition from PHPDI into Symfony

## [3.3.1] - 2021-01-23
### Stable Release
- Fix RecipeEndPoint to pass also value with escaped `@` without the second `@`

## [3.3.0] - 2021-01-22
### Stable Release
- Update to last version of States and Recipe
- Complete RecipeEndPoint to fetch services referenced into request's attribute from container to put them in workplan

## [3.2.5] - 2020-12-03
### Stable Release
- Official Support of PHP8

## [3.2.4] - 2020-11-09
### Stable Release
- Allow Command/Client to be created without output instance.

## [3.2.3] - 2020-11-09
### Stable Release
- Add Command/Client as ClientInterface implementation to use East Foundation with Symfony Command
 in CLI application.

## [3.2.2] - 2020-10-18
### Stable Release
- Add CallbackStreamFactoryInterface, and CallbackStreamFactory implements CallbackStreamFactoryInterface

## [3.2.1] - 2020-10-12
### Stable Release
- Prepare library to support also PHP8.
- Remove deprecations from PHP8.
- Remove dead code in result (getParameter() was not used).

## [3.2.0] - 2020-10-04
### Beta
- Support CookbookInterface for RecipeEndpoint
- Create ProcessorCookbook and ProcessorCookbookInterface
- Create RecipeCookbook and RecipeCookbookInterface
- Update DI to use cookbooks instead inject step in recipes, to allow overridding cookbook more easily

## [3.2.0-beta5] - 2020-10-04
### Beta
- Fix manager DI Injection

## [3.2.0-beta4] - 2020-10-04
### Beta
- Create ProcessorCookbook and ProcessorCookbookInterface
- Create RecipeCookbook and RecipeCookbookInterface
- Update DI to use cookbooks instead inject step in recipes, to allow overridding cookbook more easily

## [3.2.0-beta3] - 2020-10-02
### Beta
- Recipe extends CookbookInterface

## [3.2.0-beta2] - 2020-10-02
### Beta
- Recipe extends CookbookInterface

## [3.2.0-beta1] - 2020-10-01
### Beta
- Support CookbookInterface for RecipeEndpoint
  
## [3.1.3] - 2020-09-30
### Stable Release
- Remove useless empty state declaration in manager and recipe in East Foundation, not required with current 
  version of States.

## [3.1.2] - 2020-09-18
### Stable Release
- Update QA and CI tools

## [3.1.1] - 2020-09-11
### Stable Release
### Update
- Complete Di definition dedicated to Laminas for Symfony bundle to work out of the box with the metapackage 
  `teknoo/east-foundation-symfony`.
  
## [3.1.0] - 2020-09-11
### Stable Release
### Update
- Add Di definition dedicated to Laminas for Symfony bundle to work out of the box with the metapackage 
  `teknoo/east-foundation-symfony`.

## [3.0.4] - 2020-09-10
### Stable Release
### Update
- Remove synthetic service in Symfony DI to support last version of teknoo/bridge-phpdi-symfony

## [3.0.3] - 2020-08-25
### Stable Release
### Update
- Update libs and dev libs requirements

## [3.0.2] - 2020-07-17
### Stable Release
### Change
- Restore support to deprecated Symfony\Bundle\FrameworkBundle\Controller\Controller and only Symfony\Bundle\FrameworkBundle\Controller\AbstractController.

## [3.0.1] - 2020-07-17
### Stable Release
### Change
- Update libs requirements
- Remove express support to deprecated Symfony\Bundle\FrameworkBundle\Controller\Controller and only Symfony\Bundle\FrameworkBundle\Controller\AbstractController.
- Add travis run also with lowest dependencies.

## [3.0.0] - 2020-07-12
### Stable release
- Remove Symfony Template component (integration deprecated into symfony)
- Create EngineInterface to allow creation of adapter to any templating Engine
- Create ResultInterface to allow asynchrone template rendering for callback streaming
- Create Twig Engine implementing EngineInterface and ResultInterface
- Remove 'east.controller.service' tag (not used)
- Add east.endpoint.template to inject Twig engine adapter
- Fix services definitions
- Complete tests
- Migrate universal folder in src to src's root and remove legacy support

## [3.0.0-beta2] - 2020-07-12
### Change
- Fix services definitions
- Complete tests
- Migrate universal folder in src to src's root and remove legacy support

## [3.0.0-beta1] - 2020-07-12
### Change
- Remove Symfony Template component (integration deprecated into symfony)
- Create EngineInterface to allow creation of adapter to any templating Engine
- Create ResultInterface to allow asynchrone template rendering for callback streaming
- Create Twig Engine implementing EngineInterface and ResultInterface
- Remove 'east.controller.service' tag (not used)
- Add east.endpoint.template to inject Twig engine adapter
  
## [2.1.6] - 2020-07-09
### Update
- Remove autowiring to Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface to avoid error in Symfony DI Build
  with PHP DI without synthetic service. 

## [2.1.5] - 2020-06-28
### Update
- Replace RouterInterface to UrlGeneratorInterface

## [2.1.4] - 2020-06-08
### Update
- Require Teknoo States 4.0.9 to support PHPStan 0.12.26

## [2.1.3] - 2020-04-23
### Stable Release
### Changes
- Cancel change in 2.1.3 (Security issue with Symfony's Firewall)
- Change Symfony's East Router implementation to manage also Controller definition `ClassName::MethodName` for classic
  Symfony's controller, not implementing a Symfony's abstract but not are not a static method. 

## [2.1.2] - 2020-04-23
## [2.1.1] - 2020-04-23
### Stable Release
### Changes
- Change Symfony Kernel listener priority to be called before controller name resolver 

## [2.1.0] - 2020-03-11
### Stable Release
### Changes
- Update dev tools, migrate to PHPUnit 9.0, phploc 6.0, phpcpd 5.0 
- Support symfony/psr-http-message-bridge also 2.0
- Migrate Symfony implementation to infrastructures
- Create the interface CallbackStreamInterface to define Stream able to manage asynchronous callback content generation
- Split EastEndPointTrait, in several traits, by method' roles (Authentication, Exception, PSR Response Factory, Routing / Redirecting and Templating)
- Rework Symfony implementation to able to use any PSR 7 and PSR 17 implementation instead of Zend Diactoros
- Create an adapter of Diactoros CallbackStream implementing CallbackStreamInterface
- Add Teknoo\East\Diactoros\CallbackStreamFactory to provide a StreamFactory for Teknoo\East\Diactoros\CallbackStream
- Complete Tests
- Split `EndPointInterface` into two dedicated interfaces `RedirectingInterface` and `RenderingInterface`

## [2.1.0-beta5] - 2020-03-08
### Beta Release
Split `EndPointInterface` into two dedicated interfaces `RedirectingInterface` and `RenderingInterface`

## [2.1.0-beta4] - 2020-03-08
### Veta Release
Split `EndPointInterface` into two dedicated interfaces `RedirectingInterface` and `RenderingInterface`

## [2.1.0-beta3] - 2020-03-08
### Beta Release
Split `EndPointInterface` into two dedicated interfaces `RedirectingInterface` and `RenderingInterface`

## [2.1.0-beta2] - 2020-03-04
### Beta Release
- Create the interface CallbackStreamInterface to define Stream able to manage asynchronous callback content generation
- Split EastEndPointTrait, in several traits, by method' roles (Authentication, Exception, PSR Response Factory, Routing / Redirecting and Templating)
- Rework Symfony implementation to able to use any PSR 7 and PSR 17 implementation instead of Zend Diactoros
- Create an adapter of Diactoros CallbackStream implementing CallbackStreamInterface
- Add Teknoo\East\Diactoros\CallbackStreamFactory to provide a StreamFactory for Teknoo\East\Diactoros\CallbackStream
- Complete Tests

## [2.1.0-beta1] - 2020-03-01
### Beta Release
- Update dev tools, migrate to PHPUnit 9.0, phploc 6.0, phpcpd 5.0 
- Support symfony/psr-http-message-bridge also 2.0
- Migrate Symfony implementation to infrastructures

## [2.0.2] - 2020-02-06
### Stable Release
- Fix in Symfony Configuration the TreeBuilder Configuration to remove deprecated defintion of root. 

## [2.0.1] - 2020-01-29
### Stable Release
- Fix QA
- Require Teknoo State 4.0.1+
- Update requirement for dev tools

## [2.0.0] - 2020-01-14
### Stable Release

## [2.0.0-beta9] - 2019-12-30
### Change
- Allow developpers to pass some header into HTTP Response in EndPointInterface

## [2.0.0-beta8] - 2019-12-30
### Change
- Allow developpers to pass some header into HTTP Response in EndPointInterface

## [2.0.0-beta7] - 2019-12-30
### Change
- Update copyright
- Update router in East foundation Symfony bridge to ignore Symfony controller to avoid interferences with basic controllers

## [2.0.0-beta6] - 2019-12-23
### Change
- Fix Make definitions tools
- Fix QA issues spotted by PHPStan
- Enable PHPStan extension dedicated to support Stated classes

## [2.0.0-beta5] - 2019-11-28
### Change
- Enable PHPStan in QA Tools

## [2.0.0-beta4] - 2019-11-28
### Change
- Set `Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory` into Symfony's services definitions 
to avoid compilation error with Symfony 4.4

## [2.0.0-beta3] - 2019-11-28
### Change
- Set `Teknoo\East\Foundation\Manager\ManagerInterface` and `Teknoo\East\Foundation\Http\ClientInterface` as synthetic
services into Symfony's services definitions to avoid compilation error with Symfony 4.4

## [2.0.0-beta2] - 2019-11-27
### Change
- Support zendframework/zend-diactoros 2.2

## [2.0.0-beta1] - 2019-11-27
### Change
- PHP 7.4 is the minimum required
- Switch to typed properties
- Remove some PHP useless DockBlocks
- Replace array_merge by "..." operators for integer indexed arrays
- Restrict to Symfony 4.4 or 5.+ and remove some deprecated

## [1.0.2] - 2019-10-24
### Release
- Maintenance release, QA and update dev vendors requirements

## [1.0.1] - 2019-06-09
### Release
- Maintenance release, upgrade composer dev requirement and libs

## [1.0.0] - 2019-02-10
### Upgrade
- Remove support PHP 7.2
- Remove support Symfony 4.0 and 4.1, keep 3.4 (LTS)
- Migrate to PHPUnit 8.0
- First stable release

## [0.0.11] - 2019-01-04
### Fix
- Check technical debt - Add PHP 7.3 support

## [0.0.10] - 2018-10-27
### Fix
- Fix Symfony Router bridge to remove /index.php from url path with Sf4.
- Complete tests

## [0.0.9] - 2018-08-15
### Fix
- Fix Recipe bowl, they have an extra looping because the loop counter had a bug.
- Fix recipe compiling when several steps share the same name, firsts was lost.

## [0.0.8] - 2018-07-18
Stable release
### Update
- Update RecipeEndPoint to use the "ReserveAndBegin" behavior of Recipe's chef.
- Documentation

## [0.0.8-beta2] - 2018-06-15
### Fix
- fix processor to prevent overwriting of client, manager or request vars

## [0.0.8-beta1] - 2018-06-10
### Updated
- Use Teknoo/Recipe 1.1.0
- Replace Bowl in the Processor to call the controller and selecting good parameters from the work plan by a subrecipe,
  defined by ProcessorRecipeInterface and using a DynamicBowl to delegate this operation directly to Recipe.
- Update the Behat test to use directly the container PHP-DI.

### Add
- ProcessorRecipeInterface to define subrecipe dedicated to extract the controller from the router result, inject it 
  into the work plan to be executed by the DynamicBowl defined in the container.
- LoopDetectorInterface and LoopDetector to allow looping on the processor subrecipe unitl there all results from the
  router have not been processed.   

### Remove
- Result from the router will not been injected into the request parameter. They are only available in the work plan.

## [0.0.7] - 2018-06-02
### Release
Stable release of 0.0.7

## [0.0.7-beta5] - 2018-05-01
### Updated
- update normalizer for symfony serializer for east object to manage non scalar attribute and pass them to another normalizer (aware behavior)

## [0.0.7-beta5] - 2018-04-25
### Added
- Create normalizers interface and normalizer for symfony serializer for east object

## [0.0.7-beta4] - 2018-02-24
### Fix
- Bad return type of conditional interfaces.

## [0.0.7-beta3] - 2018-02-24
### Update
- Add interfaces to define objects able to test if two object are equals, lower or greater
- Use PHP-DI 6 instead of PHP-DI 5.

## [0.0.7-beta2] - 2018-02-14
### Update
- Update recipe end point to add to workplan all values from request, and client and server request instance (not overrided)

### Fix
- fix error when request attributes returned by request object is not an array

## [0.0.7-beta1] - 2018-02-14
### Add
- RecipeEndPoint to use a recipe as endpoint.

## [0.0.6] - 2018-01-12
### Update
- composer requirement (optional, only to use with Symfony) : require symfony/psr-http-message-bridge 1.0+ and zendframework/zend-diactoros 1.7+

## [0.0.5] - 2018-01-01
### Stable release
- Build on recipe.

## [0.0.5-beta10] - 2018-01-01
### Fix
- Rollback commit "Fix proprity in kernel event listener for symfony bridge" to fix bug with symfony firewall

## [0.0.5-beta9] - 2017-12-27
### Fix
- Optimize Kern
el Event Listener in Symfony bridge

## [0.0.5-beta8] - 2017-12-22
### Fix
- Deprecation with Symfony 3.4+

## [0.0.5-beta7] - 2017-11-28
### Fix
- Processor injects query params and body parsed into the workplan
- fix security fault to avoid query param or var from body parser overload the request attributes

## [0.0.5-beta6] - 2017-11-19
### Changed
- Symfony optional support requires now 3.4-rc1 or 4.0-rc1

## [0.0.5-beta5] - 2017-11-19
### Changed
- Remove soft dependency to twig, replaced by Symfony\Component\Templating\EngineInterface, implemented by twig bundle

## [0.0.5-beta4] - 2017-11-18
### Fixed
- Remove useless dependency to logger in the processor (sinc Recipe)
- Add Behat tests

## [0.0.5-beta3] - 2017-11-12
### Fixed
- Issue when the controller is a static method, defined in string representation.
- Symfony Client implementation, error are now thrown to be managed by the Symfony stack directly and
  use Symfony error layout
- Symfony KernelListener inject the Symfony request as argument into the PSR request to allow the processor
  to inject him when the endpoint need it
- Update the Processor to forbid overload of ServerRequest instance and Client instance by the request argument.
- Update the Processor to check if the parameter injected is of required type of the processed argument if this type is an object

### Upgraded
- Prioritized routers behavior into manager
- Middleware behavior east compliant
- Router and Processor as middleware
- Client able to update it's response via a modifier callable
- Require teknoo/states 3.2 and remove deprecated teknoo/states-life-cyclable
- Use Teknoo/recipe as base and externalise middleware managements and requests execution to this library
  The configuration of middleware stay managed in the DI.
- Interface ManagerInterface will be updated to inherits ChefInterface from teknoo/recipe.
- New manager follows this new interface and inherits the Chef implementation from teknoo/recipe.
  It manages the work plan with request and client instance. Now the manager execute only the request via the recipe,
  not manage middleware.
- Interface RecipeInterface will be added, inherits RecipeInterface from teknoo/recipe and add the method to register middleware
  into the recipe and to keep the behavior with the old ManagerInterface of this library. A default implementation is provided
- Update DI to use this new behavior and replace the last manager.
- Processor reuse Bowl component from teknoo/recipe to maps arguments of the endpoint and call it.
- Promise are now managed by teknoo/recipe too.

## [0.0.5-beta2] - 2017-08-26
### Updated
- EastEndPointTrait use now StreamCallback to render a view and not call
directly the view renderer

### Fixed
- Bad exception used in EastEndPointTrait for denied access exceptions, use now AccessDeniedHttpException.

## [0.0.5-beta1] - 2017-08-14
### Updated
- Switch to PSR7, with PHP-DI as container

## [0.0.4] - 2017-08-01
### Updated
- Update dev libraries used for this project and use now PHPUnit 6.2 for tests.

## [0.0.4-beta2] - 2017-02-26
### Fix
- Errors in EastFoundationCompilerPass when a needed service (twig, router or token_storage) is not available.

## [0.0.4-beta1] - 2017-02-25
### Release
- First beta
- Complete documentations

### Update
- Rename EastEndPointTrait to EastEndPointTrait.
- Symfony Client support deep cloning.
- KernelListener clone client before use it.

## [0.0.4-alpha13] - 2017-02-19
### Added
- Add Promise immutable object to facilitate writing of East Controller

## [0.0.4-alpha12] - 2017-02-15
### Fix
- Code style fix
- License file follow Github specs
- Add tools to checks QA, use `make qa` and `make test`, `make` to initalize the project, (or `composer update`).
- Update Travis to use this tool
- Fix QA Errors

## [0.0.4-alpha11] - 2017-01-06
### Updated
- Use last States library version

## [0.0.4-alpha10] - 2016-12-28
### Fixed
- Processor can extract POST values

## [0.0.4-alpha9] - 2016-12-28
### Fixed
- Update tests to check behavior on multiple request 
- Fix bug with symfony sub request looping to manage uncatched exception

## [0.0.4-alpha8] - 2016-12-23
### Updated
- Travis to check PHP 7.1
- Requires States 3.x libraries in beta 

## [0.0.4-alpha7] - 2016-11-19
### Removed
- Remove States Life Cyclable requirement in composer

## [0.0.4-alpha6] - 2016-11-19
### Added
- Service provider following PSR7

## [0.0.4-alpha5] - 2016-11-18
### Fixed
- Fix issue in namespace defined in composer file
- Fix CompilerPass in Symfony Bundle to support new Controller behavior
- Fix several mistakes
- fix errors in symfony bundle services.yml and add service container into router

### Updated
- Update router to find controller callable from DI container
- Complete tests on Processor in universal package

## [0.0.4-alpha4] - 2016-11-16
### Updated
- Rename the project "East Framework" to "East Foundation"
- Transform the bundle to universal package, usable with any PSR7 Framework, a Symfony Bundle is already provided.
- Behavior of Router : Must return a ResultInterface instance (a value object) with the callable to use as controller
- Behavior of Processor : Independent of Symfony, to use only Router's result.

### Added
- Support PSR-11 to be independent with Symfony
 
### Removed
- Base Symfony Controller

## [0.0.4-alpha3] - 2016-10-11
### Fix
- Remove dead code, useless with States 3.0
- Fix bad namespace in manager

## [0.0.4-alpha2] - 2016-10-11
### Fix
- Travis configuration

## [0.0.4-alpha1] - 2016-10-11
### Updated
- Migrate to States 3.0

## [0.0.3] - 2016-08-04
### Fixed
- Improve optimization on call to native function and optimized

## [0.0.2] - 2016-08-01
### Fixed
- Error when the framework is used with a lower configurations

## [0.0.1] - 2016-07-26
### Updated
- First stable release
- Improve documentation and add API Doc
- Fix CS Style

## [0.0.1-alpha3] - 2016-04-16
### Updated
    - Update Manager to use Teknoo / States to implement a defective behavior to avoid multiple requests by instance
    - Update Client to use Teknoo / States to implement a defective behavior to avoid multiple requests by instance

## [0.0.1-alpha2] - 2016-04-16
### Updated
    - update processor to ignore non available controller and log info

## [0.0.1-alpha1] - 2016-04-11
### Added
    - First release
    - Add manager to manager routers and processing request
    - Add router to analyze requests and find controller
    - Add processor to instantiate controller and execute request
    - Add client to manage and send response to client
    - Add a kernel listener to intercept response from
    - Add a abstract controller to replace symfony base controller for developpers.
