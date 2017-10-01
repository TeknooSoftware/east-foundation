#Teknoo Software - East Foundation - Change Log

##[0.0.5-beta3] - 2017-09-
###Fixed
- Issue when the controller is a static method, defined in string representation.t
- Symfony Client implementation, error are now throwed to be managed by the Symfony stack directly and
  use Symfony error layout
- Symfony KernelListener inject the Symfony request as argument into the PSR request to allow the processor
  to inject him when the endpoint need it
- Update the Processor to forbid overload of ServerRequest instance and Client instance by the request argument.
- Update the Processor to check if the parameter injected is of required type of the processed argument if this type is an object

###Upgraded
- Priorized routers behavior into manager

##[0.0.5-beta2] - 2017-08-26
###Updated
- EastEndPointTrait use now StreamCallback to render a view and not call
directly the view renderer

###Fixed
- Bad exception used in EastEndPointTrait for denied access exceptions, use now AccessDeniedHttpException.

##[0.0.5-beta1] - 2017-08-14
###Updated
- Switch to PSR11, with PHP-DI as container

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
- Service provider following PSR11

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
- Transform the bundle to universal package, usable with any PSR11 Framework, a Symfony Bundle is already provided.
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
