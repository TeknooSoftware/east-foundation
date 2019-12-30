#Teknoo Software - East Foundation - Change Log

##[2.0.0-beta8] - 2019-12-30
###Change
- Allow developpers to pass some header into HTTP Response in EndPointInterface

##[2.0.0-beta7] - 2019-12-30
###Change
- Update copyright
- Update router in East foundation Symfony bridge to ignore Symfony controller to avoid interferences with basic controllers

##[2.0.0-beta6] - 2019-12-23
###Change
- Fix Make definitions tools
- Fix QA issues spotted by PHPStan
- Enable PHPStan extension dedicated to support Stated classes

##[2.0.0-beta5] - 2019-11-28
###Change
- Enable PHPStan in QA Tools

##[2.0.0-beta4] - 2019-11-28
###Change
- Set `Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory` into Symfony's services definitions 
to avoid compilation error with Symfony 4.4

##[2.0.0-beta3] - 2019-11-28
###Change
- Set `Teknoo\East\Foundation\Manager\ManagerInterface` and `Teknoo\East\Foundation\Http\ClientInterface` as synthetic
services into Symfony's services definitions to avoid compilation error with Symfony 4.4

##[2.0.0-beta2] - 2019-11-27
###Change
- Support zendframework/zend-diactoros 2.2

##[2.0.0-beta1] - 2019-11-27
###Change
- PHP 7.4 is the minimum required
- Switch to typed properties
- Remove some PHP useless DockBlocks
- Replace array_merge by "..." operators for integer indexed arrays
- Restrict to Symfony 4.4 or 5.+ and remove some deprecated

##[1.0.2] - 2019-10-24
###Release
- Maintenance release, QA and update dev vendors requirements

##[1.0.1] - 2019-06-09
###Release
- Maintenance release, upgrade composer dev requirement and libs

##[1.0.0] - 2019-02-10
###Upgrade
- Remove support PHP 7.2
- Remove support Symfony 4.0 and 4.1, keep 3.4 (LTS)
- Migrate to PHPUnit 8.0
- First stable release

##[0.0.11] - 2019-01-04
###Fix
- Check technical debt - Add PHP 7.3 support

##[0.0.10] - 2018-10-27
###Fix
- Fix Symfony Router bridge to remove /index.php from url path with Sf4.
- Complete tests

##[0.0.9] - 2018-08-15
###Fix
- Fix Recipe bowl, they have an extra looping because the loop counter had a bug.
- Fix recipe compiling when several steps share the same name, firsts was lost.

##[0.0.8] - 2018-07-18
Stable release
###Update
- Update RecipeEndPoint to use the "ReserveAndBegin" behavior of Recipe's chef.
- Documentation

##[0.0.8-beta2] - 2018-06-15
###Fix
- fix processor to prevent overwriting of client, manager or request vars

##[0.0.8-beta1] - 2018-06-10
###Updated
- Use Teknoo/Recipe 1.1.0
- Replace Bowl in the Processor to call the controller and selecting good parameters from the work plan by a subrecipe,
  defined by ProcessorRecipeInterface and using a DynamicBowl to delegate this operation directly to Recipe.
- Update the Behat test to use directly the container PHP-DI.

###Add
- ProcessorRecipeInterface to define subrecipe dedicated to extract the controller from the router result, inject it 
  into the work plan to be executed by the DynamicBowl defined in the container.
- LoopDetectorInterface and LoopDetector to allow looping on the processor subrecipe unitl there all results from the
  router have not been processed.   

###Remove
- Result from the router will not been injected into the request parameter. They are only available in the work plan.

##[0.0.7] - 2018-06-02
###Release
Stable release of 0.0.7

##[0.0.7-beta5] - 2018-05-01
###Updated
- update normalizer for symfony serializer for east object to manage non scalar attribute and pass them to another normalizer (aware behavior)

##[0.0.7-beta5] - 2018-04-25
###Added
- Create normalizers interface and normalizer for symfony serializer for east object

##[0.0.7-beta4] - 2018-02-24
###Fix
- Bad return type of conditional interfaces.

##[0.0.7-beta3] - 2018-02-24
###Update
- Add interfaces to define objects able to test if two object are equals, lower or greater
- Use PHP-DI 6 instead of PHP-DI 5.

##[0.0.7-beta2] - 2018-02-14
###Update
- Update recipe end point to add to workplan all values from request, and client and server request instance (not overrided)

###Fix
- fix error when request attributes returned by request object is not an array

##[0.0.7-beta1] - 2018-02-14
###Add
- RecipeEndPoint to use a recipe as endpoint.

##[0.0.6] - 2018-01-12
###Update
- composer requirement (optional, only to use with Symfony) : require symfony/psr-http-message-bridge 1.0+ and zendframework/zend-diactoros 1.7+

##[0.0.5] - 2018-01-01
###Stable release
- Build on recipe.

##[0.0.5-beta10] - 2018-01-01
###Fix
- Rollback commit "Fix proprity in kernel event listener for symfony bridge" to fix bug with symfony firewall

##[0.0.5-beta9] - 2017-12-27
###Fix
- Optimize Kern
el Event Listener in Symfony bridge

##[0.0.5-beta8] - 2017-12-22
###Fix
- Deprecation with Symfony 3.4+

##[0.0.5-beta7] - 2017-11-28
###Fix
- Processor injects query params and body parsed into the workplan
- fix security fault to avoid query param or var from body parser overload the request attributes

##[0.0.5-beta6] - 2017-11-19
###Changed
- Symfony optional support requires now 3.4-rc1 or 4.0-rc1

##[0.0.5-beta5] - 2017-11-19
###Changed
- Remove soft dependency to twig, replaced by Symfony\Component\Templating\EngineInterface, implemented by twig bundle

##[0.0.5-beta4] - 2017-11-18
###Fixed
- Remove useless dependency to logger in the processor (sinc Recipe)
- Add Behat tests

##[0.0.5-beta3] - 2017-11-12
###Fixed
- Issue when the controller is a static method, defined in string representation.
- Symfony Client implementation, error are now thrown to be managed by the Symfony stack directly and
  use Symfony error layout
- Symfony KernelListener inject the Symfony request as argument into the PSR request to allow the processor
  to inject him when the endpoint need it
- Update the Processor to forbid overload of ServerRequest instance and Client instance by the request argument.
- Update the Processor to check if the parameter injected is of required type of the processed argument if this type is an object

###Upgraded
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

##[0.0.5-beta2] - 2017-08-26
###Updated
- EastEndPointTrait use now StreamCallback to render a view and not call
directly the view renderer

###Fixed
- Bad exception used in EastEndPointTrait for denied access exceptions, use now AccessDeniedHttpException.

##[0.0.5-beta1] - 2017-08-14
###Updated
- Switch to PSR7, with PHP-DI as container

##[0.0.4] - 2017-08-01
###Updated
- Update dev libraries used for this project and use now PHPUnit 6.2 for tests.

##[0.0.4-beta2] - 2017-02-26
###Fix
- Errors in EastFoundationCompilerPass when a needed service (twig, router or token_storage) is not available.

##[0.0.4-beta1] - 2017-02-25
###Release
- First beta
- Complete documentations

###Update
- Rename EastEndPointTrait to EastEndPointTrait.
- Symfony Client support deep cloning.
- KernelListener clone client before use it.

##[0.0.4-alpha13] - 2017-02-19
###Added
- Add Promise immutable object to facilitate writing of East Controller

##[0.0.4-alpha12] - 2017-02-15
###Fix
- Code style fix
- License file follow Github specs
- Add tools to checks QA, use `make qa` and `make test`, `make` to initalize the project, (or `composer update`).
- Update Travis to use this tool
- Fix QA Errors

##[0.0.4-alpha11] - 2017-01-06
###Updated
- Use last States library version

##[0.0.4-alpha10] - 2016-12-28
###Fixed
- Processor can extract POST values

##[0.0.4-alpha9] - 2016-12-28
###Fixed
- Update tests to check behavior on multiple request 
- Fix bug with symfony sub request looping to manage uncatched exception

##[0.0.4-alpha8] - 2016-12-23
###Updated
- Travis to check PHP 7.1
- Requires States 3.x libraries in beta 

##[0.0.4-alpha7] - 2016-11-19
###Removed
- Remove States Life Cyclable requirement in composer

##[0.0.4-alpha6] - 2016-11-19
###Added
- Service provider following PSR7

##[0.0.4-alpha5] - 2016-11-18
###Fixed
- Fix issue in namespace defined in composer file
- Fix CompilerPass in Symfony Bundle to support new Controller behavior
- Fix several mistakes
- fix errors in symfony bundle services.yml and add service container into router

###Updated
- Update router to find controller callable from DI container
- Complete tests on Processor in universal package

##[0.0.4-alpha4] - 2016-11-16
###Updated
- Rename the project "East Framework" to "East Foundation"
- Transform the bundle to universal package, usable with any PSR7 Framework, a Symfony Bundle is already provided.
- Behavior of Router : Must return a ResultInterface instance (a value object) with the callable to use as controller
- Behavior of Processor : Independent of Symfony, to use only Router's result.

###Added
- Support PSR-11 to be independent with Symfony
 
###Removed
- Base Symfony Controller

##[0.0.4-alpha3] - 2016-10-11
###Fix
- Remove dead code, useless with States 3.0
- Fix bad namespace in manager

##[0.0.4-alpha2] - 2016-10-11
###Fix
- Travis configuration

##[0.0.4-alpha1] - 2016-10-11
###Updated
- Migrate to States 3.0

##[0.0.3] - 2016-08-04
###Fixed
- Improve optimization on call to native function and optimized

##[0.0.2] - 2016-08-01
###Fixed
- Error when the framework is used with a lower configurations

##[0.0.1] - 2016-07-26
###Updated
- First stable release
- Improve documentation and add API Doc
- Fix CS Style

##[0.0.1-alpha3] - 2016-04-16
###Updated
    - Update Manager to use Teknoo / States to implement a defective behavior to avoid multiple requests by instance
    - Update Client to use Teknoo / States to implement a defective behavior to avoid multiple requests by instance

##[0.0.1-alpha2] - 2016-04-16
###Updated
    - update processor to ignore non available controller and log info

##[0.0.1-alpha1] - 2016-04-11
###Added
    - First release
    - Add manager to manager routers and processing request
    - Add router to analyze requests and find controller
    - Add processor to instantiate controller and execute request
    - Add client to manage and send response to client
    - Add a kernel listener to intercept response from
    - Add a abstract controller to replace symfony base controller for developpers.
