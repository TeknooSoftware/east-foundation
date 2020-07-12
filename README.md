Teknoo Software - East Foundation
=================================

[![Build Status](https://travis-ci.com/TeknooSoftware/east-foundation.svg?branch=master)](https://travis-ci.com/TeknooSoftware/east-foundation)
[![Latest Stable Version](https://poser.pugx.org/teknoo/east-foundation/v/stable)](https://packagist.org/packages/teknoo/east-foundation)
[![Latest Unstable Version](https://poser.pugx.org/teknoo/east-foundation/v/unstable)](https://packagist.org/packages/teknoo/east-foundation)
[![Total Downloads](https://poser.pugx.org/teknoo/east-foundation/downloads)](https://packagist.org/packages/teknoo/east-foundation)
[![License](https://poser.pugx.org/teknoo/east-foundation/license)](https://packagist.org/packages/teknoo/east-foundation)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)

East Foundation is a universal package to implement the [#east](http://blog.est.voyage/phpTour2015/) philosophy with 
any framework supporting PSR-11 or with Symfony 3+. :
All public method of objects must return $this or a new instance of $this.

This bundle uses PSR7 requests and responses and do automatically the conversion from Symfony's requests and responses.
So your controllers and services can be independent of Symfony. This bundle reuse internally Symfony's components
to manage routes and find controller to call.

Quick Example
-------------

    <?php

    declare(strict_types=1);
    
    use Teknoo\East\Foundation\Router\ResultInterface;
    use function DI\decorate;
    use DI\ContainerBuilder;
    use Laminas\Diactoros\ServerRequest;
    use Laminas\Diactoros\Response\TextResponse;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Teknoo\East\Foundation\Http\ClientInterface;
    use Teknoo\East\Foundation\Manager\ManagerInterface;
    use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
    use Teknoo\East\Foundation\Recipe\RecipeInterface;
    use Teknoo\East\Foundation\Router\Result;
    use Teknoo\East\Foundation\Router\RouterInterface;
    
    require_once 'vendor/autoload.php';
    
    //Simulate client, accepts responses from controller and pass them to the "framework" or lower layer to send them to
    //the browser.
    $client = new class implements ClientInterface {
        private ?ResponseInterface $response = null;
    
        public function updateResponse(callable $modifier): ClientInterface
        {
            $modifier($this, $this->response);
    
            return $this;
        }
    
        public function acceptResponse(ResponseInterface $response): ClientInterface
        {
            $this->response = $response;
    
            return $this;
        }
    
        public function sendResponse(ResponseInterface $response = null , bool $silently = false): ClientInterface
        {
            if ($response instanceof ResponseInterface) {
                $this->acceptResponse($response);
            }
    
            print (string) $response->getBody().PHP_EOL;
    
            return $this;
        }
    
        public function errorInRequest(\Throwable $throwable): ClientInterface
        {
            print $throwable->getMessage();
    
            return $this;
        }
    };
    
    //First controller / endpoint, dedicated for the request /foo
    $endPoint1 = function (ServerRequestInterface $request, ClientInterface $client) {
        $client->sendResponse(new TextResponse('request /bar, endpoint 1, value : '.$request->getQueryParams()['value']));
    };
    
    //Second controller / endpoint, dedicated for the request /bar
    $endPoint2 = function (ClientInterface $client, string $value) {
        $client->sendResponse(new TextResponse('request /bar, endpoint 2, value : '.$value));
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
            ServerRequestInterface $request ,
            ManagerInterface $manager
        ): MiddlewareInterface
        {
            $result = null;
            $uri = (string) $request->getUri();
            switch ($uri) {
                case '/foo':
                    $result = new Result($this->endPoint1);
                    break;
                case '/bar':
                    $result = new Result($this->endPoint2);
                    break;
            }
    
            $manager->updateWorkPlan([ResultInterface::class => $result]);
            $manager->continueExecution($client, $request);
    
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

This library requires :

    * PHP 7.4+
    * A PHP autoloader (Composer is recommended)
    * Teknoo/Immutable.
    * Teknoo/States.
    * Teknoo/Recipe.
    * Optional: Symfony 4.4+

News from Teknoo East Foundation 2.0
----------------------------

This library requires PHP 7.4 or newer and it's only compatible with Symfony 4.4 or newer, Some change causes bc breaks :

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
