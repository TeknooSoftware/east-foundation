<?php

namespace Teknoo\East\Framework\Router;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Teknoo\East\Framework\Http\ClientInterface;
use Teknoo\East\Framework\Manager\ManagerInterface;
use Teknoo\East\Framework\Processor\ProcessorInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

/**
 * Class Router
 * @package AppBundle\Router
 */
class Router implements RouterInterface
{
    /**
     * @var UrlMatcherInterface
     */
    private $matcher;

    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**self
     * Router constructor.
     * @param UrlMatcherInterface $urlMatcher
     * @param ProcessorInterface $processor
     */
    public function __construct(
        UrlMatcherInterface $urlMatcher,
        ProcessorInterface $processor
    ) {
        $this->matcher = $urlMatcher;
        $this->processor = $processor;
    }

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    private function matchRequest(ServerRequestInterface $request): array
    {
        try {
            return $this->matcher->match(
                str_replace('/app_dev.php', '', $request->getUri()->getPath())
            );
        } catch(ResourceNotFoundException $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function receiveRequestFromServer(
        ClientInterface $client,
        ServerRequestInterface $request,
        ManagerInterface $manager
    ): RouterInterface {
        $parameters = $this->matchRequest($request);

        if (!empty($parameters)) {
            $this->processor->executeRequest($client, $request, $parameters);

            $manager->stopPropagation();
        }

        return $this;
    }
}