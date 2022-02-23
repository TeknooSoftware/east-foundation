Teknoo Software - East Foundation
=================================

[![Latest Stable Version](https://poser.pugx.org/teknoo/east-foundation/v/stable)](https://packagist.org/packages/teknoo/east-foundation)
[![Latest Unstable Version](https://poser.pugx.org/teknoo/east-foundation/v/unstable)](https://packagist.org/packages/teknoo/east-foundation)
[![Total Downloads](https://poser.pugx.org/teknoo/east-foundation/downloads)](https://packagist.org/packages/teknoo/east-foundation)
[![License](https://poser.pugx.org/teknoo/east-foundation/license)](https://packagist.org/packages/teknoo/east-foundation)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)

East Foundation is a universal package to implement the [#east](http://blog.est.voyage/phpTour2015/) philosophy with 
any framework supporting PSR-11 or with Symfony 6.0+. :
All public method of objects must return $this or a new instance of $this.

This bundle uses PSR7 requests and responses and do automatically the conversion from Symfony's requests and responses.
So your controllers and services can be independent of Symfony. This bundle reuse internally Symfony's components
to manage routes and find controller to call.

Quick Example
-------------

    <?php

    declare(strict_types=1);
    
    use Psr\Http\Message\MessageInterface;
    use Teknoo\East\Foundation\Client\ResponseInterface;
    use Teknoo\East\Foundation\Router\ResultInterface;
    use DI\ContainerBuilder;
    use Laminas\Diactoros\ServerRequest;
    use Laminas\Diactoros\Response\TextResponse;
    use Teknoo\East\Foundation\Client\ClientInterface;
    use Teknoo\East\Foundation\Manager\ManagerInterface;
    use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
    use Teknoo\East\Foundation\Recipe\RecipeInterface;
    use Teknoo\East\Foundation\Router\Result;
    use Teknoo\East\Foundation\Router\RouterInterface;
    
    use function DI\decorate;
    
    require_once 'vendor/autoload.php';
    
    //Simulate client, accepts responses from controller and pass them to the "framework" or lower layer to send them to
    //the browser.
    $client = new class implements ClientInterface {
        private ResponseInterface | MessageInterface | null $response = null;
    
        private bool $inSilentlyMode = false;
    
        public function updateResponse(callable $modifier): ClientInterface
        {
            $modifier($this, $this->response);
    
            return $this;
        }
    
        public function acceptResponse(ResponseInterface | MessageInterface $response): ClientInterface
        {
            $this->response = $response;
    
            return $this;
        }
    
        public function sendResponse(
            ResponseInterface | MessageInterface | null $response = null,
            bool $silently = false
        ): ClientInterface
        {
            $silently = $silently || $this->inSilentlyMode;
    
            if (null !== $response) {
                $this->acceptResponse($response);
            }
    
            if (true === $silently && null === $this->response) {
                return $this;
            }
    
            if ($this->response instanceof  MessageInterface) {
                print $this->response->getBody() . PHP_EOL;
            } else {
                print $this->response . PHP_EOL;
            }
    
            return $this;
        }
    
        public function errorInRequest(Throwable $throwable, bool $silently = false): ClientInterface
        {
            print $throwable->getMessage() . PHP_EOL;
    
            return $this;
        }
    
        public function mustSendAResponse(): ClientInterface
        {
            $this->inSilentlyMode = false;
    
            return $this;
        }
    
        public function sendAResponseIsOptional(): ClientInterface
        {
            $this->inSilentlyMode = true;
    
            return $this;
        }
    };
    
    //First controller / endpoint, dedicated for the request /foo
    $endPoint1 = static function (MessageInterface $message, ClientInterface $client) {
        $client->sendResponse(
            new TextResponse('request /bar, endpoint 1, value : ' . $message->getQueryParams()['value'])
        );
    };
    
    //Second controller / endpoint, dedicated for the request /bar
    $endPoint2 = static function (ClientInterface $client, string $value) {
        $client->sendResponse(
            new class ($value) implements ResponseInterface {
                public function __construct(
                    private string $value,
                ) {
                }
    
                public function __toString()
                {
                    return "request /bar, endpoint 2, value : {$this->value}";
                }
            }
        );
    };
    
    /**
     * Simulate router
     */
    $router = new class($endPoint1, $endPoint2) implements RouterInterface {
        /**
         * @var callable
         */
        private $endPoint1;
    
        /**
         * @var callable
         */
        private $endPoint2;
    
        public function __construct(callable $endPoint1 , callable $endPoint2)
        {
            $this->endPoint1 = $endPoint1;
            $this->endPoint2 = $endPoint2;
        }
    
        public function execute(
            ClientInterface $client ,
            MessageInterface $message,
            ManagerInterface $manager
        ): MiddlewareInterface
        {
            $uri = (string) $message->getUri();
    
            $manager->updateWorkPlan([
                ResultInterface::class => match ($uri) {
                    '/foo' => new Result($this->endPoint1),
                    '/bar' => new Result($this->endPoint2),
                },
            ]);
            $manager->continueExecution($client, $message);
    
            return $this;
        }
    };
    
    $builder = new ContainerBuilder();
    $builder->addDefinitions('src/di.php');
    $builder->addDefinitions([
        RouterInterface::class => $router,
    
        RecipeInterface::class => decorate(function ($previous) use ($router) {
            if ($previous instanceof RecipeInterface) {
                $previous = $previous->registerMiddleware(
                    $router,
                    RouterInterface::MIDDLEWARE_PRIORITY
                );
            }
    
            return $previous;
        })
    ]);
    
    $container = $builder->build();
    
    //Simulate Server request reception
    $request1 = new ServerRequest([], [], '/foo', 'GET');
    $request1 = $request1->withQueryParams(['value' => 'bar']);
    $request2 = new ServerRequest([], [], '/bar', 'GET');
    $request2 = $request2->withQueryParams(['value' => 'foo']);
    
    $manager = $container->get(ManagerInterface::class);
    $manager->receiveRequest($client, $request1);
    //Print: request /bar, endpoint 1, value : bar
    $manager->receiveRequest($client, $request2);
    //Print: request /bar, endpoint 2, value : foo

Support this project
---------------------

This project is free and will remain free, but it is developed on my personal time. 
If you like it and help me maintain it and evolve it, don't hesitate to support me on [Patreon](https://patreon.com/teknoo_software).
Thanks :) Richard. 

Installation & Requirements
---------------------------
To install this library with composer, run this command :

    composer require teknoo/east-foundation
    
To install with Symfony

    composer require teknoo/composer-install
    composer require teknoo/east-foundation-symfony  

This library requires :

    * PHP 8.1+
    * A PHP autoloader (Composer is recommended)
    * Teknoo/Immutable.
    * Teknoo/States.
    * Teknoo/Recipe.
    * Optional: Symfony 6.0+

News from Teknoo East Foundation 5.0
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

News from Teknoo East Foundation 4.0
------------------------------------

This library requires PHP 7.4 or newer and it's only compatible with Symfony 4.4 or newer :

- Switch to States 4.1.9 and PHPStan 0.12.79
- Prepare library to be used in non HTTP context
- Use MessageInterface instead of ServerRequestInterface
- Cookbook and ProcessorCookbook use BaseCookbookTrait
- Add PSR11 Message only implementation
- Add MessageFactory
- Update Client Interface to use MessageInterface instead of RequestInterface
- Add Recipe executor dedicated to Symfony Messenger
- Add Client dedicated to Symfony Messenger
- Remove some public services

News from Teknoo East Foundation 3.0
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

News from Teknoo East Foundation 2.0
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


Credits
-------
Richard Déloge - <richarddeloge@gmail.com> - Lead developer.
Teknoo Software - <http://teknoo.software>

About Teknoo Software
---------------------
**Teknoo Software** is a PHP software editor, founded by Richard Déloge. 
Teknoo Software's DNA is simple : Provide to our partners and to the community a set of high quality services or software,
 sharing knowledge and skills.

License
-------
East Foundation is licensed under the MIT License - see the licenses folder for details

Contribute :)
-------------

You are welcome to contribute to this project. [Fork it on Github](CONTRIBUTING.md)
