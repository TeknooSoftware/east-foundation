Teknoo Software - East Foundation
=================================

[![Latest Stable Version](https://poser.pugx.org/teknoo/east-foundation/v/stable)](https://packagist.org/packages/teknoo/east-foundation)
[![Latest Unstable Version](https://poser.pugx.org/teknoo/east-foundation/v/unstable)](https://packagist.org/packages/teknoo/east-foundation)
[![Total Downloads](https://poser.pugx.org/teknoo/east-foundation/downloads)](https://packagist.org/packages/teknoo/east-foundation)
[![License](https://poser.pugx.org/teknoo/east-foundation/license)](https://packagist.org/packages/teknoo/east-foundation)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)

East Foundation is a universal package to implement the [#east](http://blog.est.voyage/phpTour2015/) philosophy with 
any framework supporting `PSR 11`, `PSR 7` or with Symfony 6.4+ : All public method of objects must return `$this` or a 
new instance of `$this`.

This bundle uses `PSR 7` requests and responses and do automatically the conversion from Symfony's requests and responses.
So your controllers and services can be independent of Symfony. This bundle reuse internally Symfony's components
to manage routes and find controller to call. It is also designed to be used with other framework.

It can be also used for workers :
* Triggering asynchronous tasks (thanks to pcntl) for timers.
* Setting up a worker health check.
* Provides non blocking sleep method.

This library is built on the Recipe library, and redefine only some interfaces to be more comprehensive with HTTP 
context :
* Middleware are actions, but must implement a specific interface.
* The HTTP workflow is defined into a Recipe, able to be extended.
* Chef became a manager, to execute the workflow when a request is accepted.
* Usable with any `PSR 11` Framework, Symfony implementation is also provided.
* Supports `PSR 15` handler and middleware
* Supports `PSR 20` and provides a PSR-20 implementation

A complete documentation is available in [documentation/README.md](documentation/README.md)

Support this project
---------------------
This project is free and will remain free. It is fully supported by the activities of the EIRL.
If you like it and help me maintain it and evolve it, don't hesitate to support me on
[Patreon](https://patreon.com/teknoo_software) or [Github](https://github.com/sponsors/TeknooSoftware).

Thanks :) Richard.

Credits
-------
EIRL Richard Déloge - <https://deloge.io> - Lead developer.
SASU Teknoo Software - <https://teknoo.software>

About Teknoo Software
---------------------
**Teknoo Software** is a PHP software editor, founded by Richard Déloge, as part of EIRL Richard Déloge.
Teknoo Software's goals : Provide to our partners and to the community a set of high quality services or software,
sharing knowledge and skills.

License
-------
East Foundation is licensed under the MIT License - see the licenses folder for details.

Installation & Requirements
---------------------------
To install [follow these instructions](documentation/install.md).

This library requires :

    * PHP 8.1+
    * A PHP autoloader (Composer is recommended)
    * Teknoo/Immutable.
    * Teknoo/States.
    * Teknoo/Recipe.
    * Optional: Symfony 6.4+

News from Teknoo East Foundation 7.x
------------------------------------
This library requires PHP 8.1 or newer and it's only compatible with Symfony 6.3 or newer :
- PHP-DI 7 or newer
- Supports `PSR-20` with the `DatesService`.
- Supports `PSR-15` with add to new `Recipe Bowl` type :
  - `FiberHandlerBowl` (and `FiberMiddlewareBowl`) to support `PSR 15` Requests handlers into a recipe.
  - `MiddlewareBowl` (and `FiberMiddlewareBowl`) to support `PSR 15` middleware into a recipe.
- Add `Teknoo\East\Foundation\Normalizer\Object\GroupsTrait
- Add a pseudo non blocking Sleep service, build on timer
- Triggering asynchronous tasks (thanks to pcntl) for timers.
- Setting up a worker health check.
- Provides non blocking sleep method.

News from Teknoo East Foundation 6.x
------------------------------------
This library requires PHP 8.1 or newer and it's only compatible with Symfony 6.1 or newer :

- Constants are final in `SessionMiddleware`, processors, routers and sessions components.
- Use readonly for immutables objects (`Processor` and routers' results).
- Support Fibers in main `Cookbook` and `RecipeEndPoint`. `RecipeEndPoint` accepts also bowl
  and `FiberRecipeBowl`.
- Remove support of PHP 8.0 and Symfony 5.4 and below.

News from Teknoo East Foundation 5.x
------------------------------------
This library requires PHP 8.0 or newer and it's only compatible with Symfony 5.2 or newer :

- Migrate to PHP 8.0+
- Remove support of Symfony 4.4, only 5.2+
- Constructor Property Promotion
- Non-capturing catches
- switch to str_contains
- Messenger's executor use an empty manager and clone it
- Add method to configure client's behavior when a it must send a missing response (silently or throw an exception)
 - Add `ClientInterface::mustSendAResponse`
 - Add `ClientInterface::sendAResponseIsOptional`
- Processor will configure in non silent mode if a compatible callable is available and was returned by Router
 - This behavior can be disable by set `teknoo.east.client.must_send_response` to false in DI
- Move ClientInterface to `Teknoo\East\Foundation\Client` from `Teknoo\East\Foundation\Http`
- Add `Teknoo\East\Foundation\Client\ResultInterface`
- `ClientInterface` accept also ResultInterface instead PSR's message
- All clients implementations adopts new client interfaces
- Symfony Clients implementations supports `ResultInterface` and `JsonSerializable` responses

News from Teknoo East Foundation 4.x
------------------------------------
This library requires PHP 7.4 or newer and it's only compatible with Symfony 4.4 or newer :

- Switch to States 4.1.9 and PHPStan 0.12.79
- Prepare library to be used in non HTTP context
- Use MessageInterface instead of ServerRequestInterface
- Cookbook and ProcessorCookbook use BaseCookbookTrait
- Add `PSR 11` Message only implementation
- Add MessageFactory
- Update Client Interface to use MessageInterface instead of RequestInterface
- Add Recipe executor dedicated to Symfony Messenger
- Add Client dedicated to Symfony Messenger
- Remove some public services

News from Teknoo East Foundation 3.x
------------------------------------
This library requires PHP 7.4 or newer and it's only compatible with Symfony 4.4 or newer :

- Remove Symfony Template component (integration deprecated into symfony)
- Create EngineInterface to allow creation of adapter to any templating Engine
- Create ResultInterface to allow asynchrone template rendering for callback streaming
- Create Twig Engine implementing EngineInterface and ResultInterface
- Remove 'east.controller.service' tag (not used)
- Add east.endpoint.template to inject Twig engine adapter
- Fix services definitions
- Complete tests
- Migrate universal folder in src to src's root and remove legacy support

News from Teknoo East Foundation 2.x
------------------------------------
This library requires PHP 7.4 or newer and it's only compatible with Symfony 4.4 or newer :

- PHP 7.4 is the minimum required
- Switch to typed properties
- Remove some PHP useless DockBlocks
- Replace array_merge by "..." operators for integer indexed arrays
- Support zendframework/zend-diactoros 2.2
- Restrict to Symfony 4.4 or 5.+ and remove some deprecated
- Set `Teknoo\East\Foundation\Manager\ManagerInterface` and `Teknoo\East\Foundation\Http\ClientInterface` as synthetic
services into Symfony's services definitions to avoid compilation error with Symfony 4.4
- Set `Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory` into Symfony's services definitions 
to avoid compilation error with Symfony 4.4
- Enable PHPStan in QA Tools and disable PHPMD

Contribute :)
-------------
You are welcome to contribute to this project. [Fork it on Github](CONTRIBUTING.md)
