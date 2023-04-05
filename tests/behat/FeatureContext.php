<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Foundation\Behat;

use Behat\Behat\Context\Context;
use DI\Container;
use JsonSerializable;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Teknoo\East\Foundation\Command\Executor;
use Teknoo\East\Foundation\Liveness\Exception\TimeLimitReachedException;
use Teknoo\East\Foundation\Liveness\PingService;
use Teknoo\East\Foundation\Liveness\TimeoutService;
use Teknoo\Recipe\Bowl\FiberRecipeBowl;
use Teknoo\East\Foundation\EndPoint\RecipeEndPoint;
use Teknoo\East\Foundation\Recipe\CookbookInterface;
use Teknoo\East\Foundation\Recipe\Recipe;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Client\ResponseInterface as EastResponse;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\East\Foundation\Router\Result;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Throwable;

use function dirname;
use function file_put_contents;
use function json_decode;
use function parse_str;
use function set_time_limit;
use function time;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private ?Container $container = null;

    private ?RouterInterface $router = null;

    private ?ClientInterface $client = null;

    public ResponseInterface|EastResponse|null $response = null;

    /**
     * @va \Throwable
     */
    public ?Throwable $error = null;

    private ?string $pingFile = null;

    private ?Executor $executor = null;

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
        set_time_limit(0);
        $this->error = null;
        $this->pingFile = null;
        $this->container = include(dirname(__DIR__, 2) . '/src/generator.php');
        $this->container->set('teknoo.east.client.must_send_response', true);
    }

    /**
     * @Given client are configured to ignore missing response
     */
    public function clientAreConfiguredToIgnoreMissingResponse()
    {
        $this->container->set('teknoo.east.client.must_send_response', false);
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
                MessageInterface $message,
                ManagerInterface $manager
            ): MiddlewareInterface {
                if (!$message instanceof ServerRequestInterface) {
                    return $this;
                }

                $path = $message->getUri()->getPath();

                if (isset($this->routes[$path])) {
                    $result = new Result($this->routes[$path]);
                    $manager->updateWorkPlan([ResultInterface::class => $result]);

                    $manager->updateMessage($message);
                };

                return $this;
            }
        };

        $this->container->set(RouterInterface::class, $this->router);
    }

    /**
     * @Given The router can process the request :url to controller :controllerName
     */
    public function theRouterCanProcessTheRequestToController($url, $controllerName)
    {
        $controller = $controllerName;
        if ('closureFoo' === $controllerName) {
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

        $this->router->registerRoute($url, $controller);
    }

    private function createRecipeToReturnResponse(string $url, string $controllerName, string $type, bool $inFiber)
    {
        $controller = $controllerName;
        if ('barFoo' === $controllerName) {
            $recipe = new Recipe;
            $recipe = $recipe->cook(function (ClientInterface $client, ServerRequest $request, $test) use ($type) {
                if ('psr' === $type) {
                    $client->acceptResponse(
                        new TextResponse($test . $request->getUri())
                    );
                } elseif ('east' === $type) {
                    $client->acceptResponse(
                        new class($test) implements EastResponse {
                            public function __construct(
                                private string $value,
                            ) {
                            }

                            public function __toString(): string
                            {
                                return $this->value;
                            }
                        }
                    );
                }elseif ('json' === $type) {
                    $client->acceptResponse(
                        new class($test) implements EastResponse, JsonSerializable {
                            public function __construct(
                                private string $value,
                            ) {
                            }

                            public function __toString(): string
                            {
                                return $this->value;
                            }

                            public function jsonSerialize(): mixed
                            {
                                return ['foo' => $this->value];
                            }
                        }
                    );
                }
            }, 'body');

            if (false === $inFiber) {
                $controller = new RecipeEndPoint($recipe);
            } else {
                $controller = new RecipeEndPoint(new FiberRecipeBowl($recipe, 0));
            }
        }

        if ('fooBar' === $controllerName) {
            $recipe = new Recipe;
            $recipe = $recipe->cook(function (ClientInterface $client, ServerRequest $request, $test) {

            }, 'body');
            if (false === $inFiber) {
                $controller = new RecipeEndPoint($recipe);
            } else {
                $controller = new RecipeEndPoint(new FiberRecipeBowl($recipe, 0));
            }
        }

        $this->router->registerRoute($url, $controller);
    }

    /**
     * @Given The router can process the request :url to recipe :controllerName to return a :type response
     */
    public function theRouterCanProcessTheRequestToRecipeToReturnResponse($url, $controllerName, $type)
    {
        $this->createRecipeToReturnResponse($url, $controllerName, $type, false);
    }

    /**
     * @Given The router can process the request :url to recipe :controllerName in a fiber to return a :type response
     */
    public function theRouterCanProcessTheRequestToRecipeInAFiberToReturnResponse($url, $controllerName, $type)
    {
        $this->createRecipeToReturnResponse($url, $controllerName, $type, true);
    }

    private function createClient(): void
    {
        $this->client = new class($this) implements ClientInterface {
            private FeatureContext $context;

            private bool $inSilentlyMode = false;

            public function __construct(FeatureContext $context)
            {
                $this->context = $context;
            }

            public function updateResponse(callable $modifier): ClientInterface
            {
                $modifier($this, $this->context->response);

                return $this;
            }

            public function acceptResponse(EastResponse | MessageInterface | null $response): ClientInterface
            {
                $this->context->response = $response;

                return $this;
            }

            public function sendResponse(
                EastResponse | MessageInterface | null $response = null,
                bool $silently = false
            ): ClientInterface {
                $silently = $silently || $this->inSilentlyMode;

                if (!empty($response)) {
                    $this->context->response = $response;
                }

                if (false === $silently && !$this->context->response instanceof MessageInterface) {
                    throw new RuntimeException('Error, there are no response');
                }

                return $this;
            }

            public function errorInRequest(Throwable $throwable, bool $silently = false): ClientInterface
            {
                $this->context->error = $throwable;

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
    }

    /**
     * @When The server will receive the request :url
     */
    public function theServerWillReceiveTheRequest($url)
    {
        $manager = new Manager($this->container->get(CookbookInterface::class));

        $this->response = null;
        $this->error = null;

        $this->createClient();;

        $request = new ServerRequest();
        $request = $request->withUri(new \Laminas\Diactoros\Uri($url));
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
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
     * @Then The client must accept a psr response
     */
    public function theClientMustAcceptAPSRResponse()
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertNull($this->error);
    }

    /**
     * @Then The client must accept a east response
     */
    public function theClientMustAcceptAEastResponse()
    {
        Assert::assertInstanceOf(EastResponse::class, $this->response);
        Assert::assertNull($this->error);
    }

    /**
     * @Then The client must accept a json response
     */
    public function theClientMustAcceptAJsonResponse()
    {
        Assert::assertInstanceOf(JsonSerializable::class, $this->response);
        Assert::assertNull($this->error);
    }

    /**
     * @Then I should get as response :value
     */
    public function iShouldGetAsResponse ($value)
    {
        if ($this->response instanceof ResponseInterface) {
            Assert::assertEquals($value, (string)$this->response->getBody());

            return;
        }

        if ($this->response instanceof JsonSerializable) {
            Assert::assertEquals(
                json_decode($value, true),
                json_decode((string) json_encode($this->response), true)
            );

            return;
        }

        if ($this->response instanceof EastResponse) {
            Assert::assertEquals($value, (string) $this->response);

            return;
        }

        throw new RuntimeException('Response not managed');
    }

    /**
     * @Then I should get nothing
     */
    public function iShouldGetNothing()
    {
        $this->client->sendResponse();
        Assert::assertNull($this->response);
    }

    /**
     * @Then The client must accept an error
     */
    public function theClientMustAcceptAnError()
    {
        Assert::assertNull($this->response);
        Assert::assertInstanceOf(Throwable::class, $this->error);
    }

    /**
     * @Then The client must throw an exception
     */
    public function theClientMustThrowAnException()
    {
        $errorCatched = false;
        try {
            $this->client->sendResponse();
        } catch (Throwable $error) {
            $errorCatched = true;
        }
        Assert::assertTrue($errorCatched);
        Assert::assertNull($this->response);
    }

    /**
     * @Given a cli agent
     */
    public function aCliAgent()
    {
        $this->response = null;
        $this->error = null;

        $this->executor = new Executor(
            new Manager(null),
        );

        $this->createClient();
    }

    /**
     * @Given a liveness behavior build on event on a file :fileName
     */
    public function aLivenessBehaviorBuildOnEventOnAFile(string $fileName)
    {
        $filePath = $this->pingFile = dirname(__DIR__, 1) . "/var/$fileName";
        $this->container
            ->get(PingService::class)
            ->register(
                id: "behat-ping",
                callback: fn () => file_put_contents($filePath, time()),
            );
    }

    /**
     * @Given each task must be limited in time of :value seconds and killed when they exceed it.
     */
    public function eachTaskMustBeLimitedInTimeOfSecondsAndKilledWhenTheyExceedIt(int $value)
    {
        $this->container
            ->get(TimeoutService::class)
            ->enable($value);
    }

    /**
     * @Then task must be finished
     */
    public function taskMustBeFinished()
    {
        Assert::assertInstanceOf(
            EastResponse::class,
            $this->response,
        );
    }

    /**
     * @Then no exception must be throwed
     */
    public function noExceptionMustBeThrowed()
    {
        Assert::assertNull($this->error);
    }

    /**
     * @When the agent start a short task
     */
    public function theAgentStartAShortTask()
    {
        $recipe = new Recipe();
        $recipe = $recipe->cook(
            action: function (
                PingService $service,
                ClientInterface $client,
            ): void {
                $service->ping();
                $expectedTime = time() + 2;
                while (time() < $expectedTime) {
                    $x = str_repeat('x', 100000);
                }
                $service->ping();
                $client->acceptResponse(
                   new class implements EastResponse {
                       public function __toString(): string
                       {
                           return 'ok';
                       }
                   }
                );
            },
            name: 'shortTask'
        );

        $this->executor->execute(
            $recipe,
            new Request(),
            $this->client,
            [
                PingService::class => $this->container->get(PingService::class),
            ],
        );
    }

    /**
     * @When the agent start a too long task
     */
    public function theAgentStartATooLongTask()
    {
        $recipe = new Recipe();
        $recipe = $recipe->cook(
            action: function (
                PingService $service,
                ClientInterface $client,
            ): void {
                $service->ping();
                $expectedTime = time() + 60;
                while (time() < $expectedTime) {
                    $x = str_repeat('x', 100000);
                }
                $service->ping();
                $client->acceptResponse(
                    new class implements EastResponse {
                        public function __toString(): string
                        {
                            return 'ok';
                        }
                    }
                );
            },
            name: 'shortTask'
        );

        $this->executor->execute(
            $recipe,
            new Request(),
            $this->client,
            [
                PingService::class => $this->container->get(PingService::class),
            ],
        );
    }

    /**
     * @Then An exception must be catched
     */
    public function anExceptionMustBeCatched()
    {
        Assert::assertInstanceOf(
            TimeLimitReachedException::class,
            $this->error,
        );
    }

    /**
     * @Then the task must be not finished
     */
    public function theTaskMustBeNotFinished()
    {
        Assert::assertNull($this->response);
    }
}
