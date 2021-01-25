<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\EndPoint\RecipeEndPoint;
use Teknoo\East\Foundation\Recipe\RecipeCookbookInterface;
use Teknoo\East\Foundation\Recipe\Recipe;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\East\Foundation\Router\Result;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /**
     * @var \DI\Container
     */
    private $container;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ResponseInterface
     */
    public $response;

    /**
     * @va \Throwable
     */
    public $error;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @Given I have DI initialized
     */
    public function iHaveDiInitialized()
    {
        $this->container = include(\dirname(\dirname(__DIR__)) . '/src/generator.php');
    }

    /**
     * @Given I register a router
     */
    public function iRegisterARouter()
    {
        $this->router = new class implements RouterInterface {
            private $routes = [];

            public function registerRoute(string $route, callable $controller): self
            {
                $this->routes[$route] = $controller;

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function execute(
                ClientInterface $client,
                ServerRequestInterface $request,
                ManagerInterface $manager
            ): MiddlewareInterface {
                $path = $request->getUri()->getPath();

                if (isset($this->routes[$path])) {
                    $result = new Result($this->routes[$path]);
                    $manager->updateWorkPlan([ResultInterface::class => $result]);

                    $manager->continueExecution($client, $request);
                };

                return $this;
            }
        };

        $this->container->set(RouterInterface::class, $this->router);
    }

    /**
     * @Given The router can process the request :arg1 to controller :arg2
     */
    public function theRouterCanProcessTheRequestToController($arg1, $arg2)
    {
        $controller = $arg2;
        if ('closureFoo' === $arg2) {
            $controller = function (ClientInterface $client, ServerRequest $request) {
                $params = $request->getQueryParams();

                if (isset($params['test'])) {
                    $client->acceptResponse(
                        new TextResponse($params['test'])
                    );
                } else {
                    $client->errorInRequest(new RuntimeException('Missing test parameter in the query'));
                }
            };
        }

        $this->router->registerRoute($arg1, $controller);
    }

    /**
     * @Given The router can process the request :arg1 to recipe :arg2
     */
    public function theRouterCanProcessTheRequestToRecipe($arg1, $arg2)
    {
        $controller = $arg2;
        if ('barFoo' === $arg2) {
            $recipe = new Recipe;
            $recipe = $recipe->cook(function (ClientInterface $client, ServerRequest $request, $test) {
                $client->acceptResponse(
                    new TextResponse($test.$request->getUri())
                );
            }, 'body');
            $controller = new RecipeEndPoint($recipe);
        }

        $this->router->registerRoute($arg1, $controller);
    }

    /**
     * @When The server will receive the request :arg1
     */
    public function theServerWillReceiveTheRequest($arg1)
    {
        $manager = new Manager($this->container->get(RecipeCookbookInterface::class));

        $this->response = null;
        $this->error = null;

        $this->client = new class($this) implements ClientInterface {
            /**
             * @var FeatureContext
             */
            private $context;

            /**
             *  constructor.
             * @param FeatureContext $context
             */
            public function __construct(FeatureContext $context)
            {
                $this->context = $context;
            }

            /**
             * @inheritDoc
             */
            public function updateResponse(callable $modifier): ClientInterface
            {
                $modifier($this, $this->context->response);

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function acceptResponse(ResponseInterface $response): ClientInterface
            {
                $this->context->response = $response;

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function sendResponse(ResponseInterface $response = null, bool $silently = false): ClientInterface
            {
                if (!empty($response)) {
                    $this->context->response = $response;
                }

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function errorInRequest(\Throwable $throwable, bool $silently = false): ClientInterface
            {
                $this->context->error = $throwable;

                return $this;
            }
        };

        $request = new ServerRequest();
        $request = $request->withUri(new \Zend\Diactoros\Uri($arg1));
        $query = [];
        \parse_str($request->getUri()->getQuery(), $query);
        $request = $request->withQueryParams($query);

        $manager->receiveRequest(
            $this->client,
            $request
        );
    }

    /**
     * @Then The client must not accept a response.
     */
    public function theClientMustNotAcceptAResponse()
    {
        Assert::assertNull($this->response);
        Assert::assertNull($this->error);
    }

    /**
     * @Then The client must accept a response
     */
    public function theClientMustAcceptAResponse()
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertNull($this->error);
    }

    /**
     * @Then I should get :arg1
     */
    public function iShouldGet($arg1)
    {
        Assert::assertEquals($arg1, (string) $this->response->getBody());
    }

    /**
     * @Then The client must accept an error
     */
    public function theClientMustAcceptAnError()
    {
        Assert::assertNull($this->response);
        Assert::assertInstanceOf(\Throwable::class, $this->error);
    }
}
