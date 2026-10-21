<?php

/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\FoundationBundle\Router;

use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ChainRequestMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher\AttributesRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\PathRequestMatcher;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Http\AccessMap;
use Symfony\Component\Security\Http\Firewall\AccessListener;
use Teknoo\East\FoundationBundle\Router\AccessCheckerInterface;
use Teknoo\East\FoundationBundle\Router\AccessControlChecker;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(AccessControlChecker::class)]
class AccessControlCheckerTest extends TestCase
{
    private AccessMap $accessMap;

    private TokenStorage $tokenStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accessMap = new AccessMap();
        $this->tokenStorage = new TokenStorage();
    }

    private function buildChecker(): AccessControlChecker
    {
        $accessDecisionManager = new AccessDecisionManager([
            new RoleVoter(),
            new AuthenticatedVoter(new AuthenticationTrustResolver()),
        ]);

        return new AccessControlChecker(
            new AccessListener($this->tokenStorage, $accessDecisionManager, $this->accessMap),
            $this->createStub(HttpKernelInterface::class),
        );
    }

    /**
     * @param array<int, string> $roles
     */
    private function authenticate(array $roles): void
    {
        $this->tokenStorage->setToken(
            new UsernamePasswordToken(new InMemoryUser('john', null, $roles), 'main', $roles)
        );
    }

    /**
     * @param array<string, string> $server
     */
    private function buildLiveRequest(
        string $uri = 'https://foo.com/_components/UserProfile',
        array $server = [],
    ): ServerRequest {
        $request = Request::create($uri, 'POST', ['data' => '{}'], [], [], $server);

        return (new ServerRequest(uri: $uri, method: 'POST'))->withAttribute('request', $request);
    }

    public function testCheckWithoutRule(): void
    {
        $this->accessMap->add(new PathRequestMatcher('^/admin'), ['ROLE_ADMIN']);

        $checker = $this->buildChecker();
        $this->assertSame(
            $checker,
            $checker->check($this->buildLiveRequest(), '/user/profile/456', ['_route' => 'user_profile']),
        );
    }

    public function testCheckWithPublicAccessWithoutToken(): void
    {
        $this->accessMap->add(new PathRequestMatcher('^/login'), [AuthenticatedVoter::PUBLIC_ACCESS]);

        $this->assertInstanceOf(
            AccessCheckerInterface::class,
            $this->buildChecker()->check($this->buildLiveRequest(), '/login', []),
        );
    }

    public function testCheckGranted(): void
    {
        $this->accessMap->add(new PathRequestMatcher('^/admin'), ['ROLE_ADMIN']);
        $this->authenticate(['ROLE_USER', 'ROLE_ADMIN']);

        $this->assertInstanceOf(
            AccessCheckerInterface::class,
            $this->buildChecker()->check($this->buildLiveRequest(), '/admin/users', ['_route' => 'admin_users']),
        );
    }

    public function testCheckGrantedWithOneOfSeveralRoles(): void
    {
        $this->accessMap->add(new PathRequestMatcher('^/admin'), ['ROLE_ADMIN', 'ROLE_MANAGER']);
        $this->authenticate(['ROLE_USER', 'ROLE_MANAGER']);

        $this->assertInstanceOf(
            AccessCheckerInterface::class,
            $this->buildChecker()->check($this->buildLiveRequest(), '/admin/users', []),
        );
    }

    public function testCheckDenied(): void
    {
        $this->accessMap->add(new PathRequestMatcher('^/admin'), ['ROLE_ADMIN']);
        $this->authenticate(['ROLE_USER']);

        try {
            $this->buildChecker()->check($this->buildLiveRequest(), '/admin/users', ['_route' => 'admin_users']);
        } catch (AccessDeniedException $exception) {
            // As thrown by the `AccessListener` of the firewall, for a sub-request of the page
            $this->assertSame(['ROLE_ADMIN'], $exception->getAttributes());
            $this->assertInstanceOf(Request::class, $subject = $exception->getSubject());
            $this->assertSame('/admin/users', $subject->getPathInfo());
            $this->assertSame('POST', $subject->getMethod());
            $this->assertSame('foo.com', $subject->getHost());
            $this->assertSame(['_route' => 'admin_users'], $subject->attributes->all());
            $this->assertSame([], $subject->request->all());

            return;
        }

        $this->fail('The access must be denied');
    }

    public function testCheckDeniedWithoutToken(): void
    {
        $this->accessMap->add(new PathRequestMatcher('^/admin'), ['ROLE_ADMIN']);

        $this->expectException(AccessDeniedException::class);
        $this->buildChecker()->check($this->buildLiveRequest(), '/admin/users', []);
    }

    public function testCheckDeniedWithABaseUrl(): void
    {
        $this->accessMap->add(new PathRequestMatcher('^/admin'), ['ROLE_ADMIN']);
        $this->authenticate(['ROLE_USER']);

        $request = $this->buildLiveRequest(
            'https://foo.com/index.php/_components/UserProfile',
            ['SCRIPT_NAME' => '/index.php', 'SCRIPT_FILENAME' => '/var/www/public/index.php'],
        );

        $this->expectException(AccessDeniedException::class);
        $this->buildChecker()->check($request, '/admin/users', []);
    }

    public function testCheckDeniedByARuleOnTheRoute(): void
    {
        $this->accessMap->add(new AttributesRequestMatcher(['_route' => 'admin_dashboard']), ['ROLE_ADMIN']);
        $this->authenticate(['ROLE_USER']);

        $this->expectException(AccessDeniedException::class);
        $this->buildChecker()->check($this->buildLiveRequest(), '/dashboard', ['_route' => 'admin_dashboard']);
    }

    public function testCheckGrantedByARuleOnAnotherRoute(): void
    {
        $this->accessMap->add(new AttributesRequestMatcher(['_route' => 'admin_dashboard']), ['ROLE_ADMIN']);
        $this->authenticate(['ROLE_USER']);

        $this->assertInstanceOf(
            AccessCheckerInterface::class,
            $this->buildChecker()->check($this->buildLiveRequest(), '/dashboard', ['_route' => 'user_dashboard']),
        );
    }

    public function testCheckDeniedByARuleOnTheMethodOfTheLiveRequest(): void
    {
        $this->accessMap->add(
            new ChainRequestMatcher([new PathRequestMatcher('^/edit'), new MethodRequestMatcher('POST')]),
            ['ROLE_ADMIN'],
        );
        $this->authenticate(['ROLE_USER']);

        $this->expectException(AccessDeniedException::class);
        $this->buildChecker()->check($this->buildLiveRequest(), '/edit/42', []);
    }

    public function testCheckGrantedByARuleOnAnotherMethod(): void
    {
        // The page is checked with the method of the Live Component request
        $this->accessMap->add(
            new ChainRequestMatcher([new PathRequestMatcher('^/edit'), new MethodRequestMatcher('GET')]),
            ['ROLE_ADMIN'],
        );
        $this->authenticate(['ROLE_USER']);

        $this->assertInstanceOf(
            AccessCheckerInterface::class,
            $this->buildChecker()->check($this->buildLiveRequest(), '/edit/42', []),
        );
    }

    public function testCheckDeniedWithoutSymfonyRequest(): void
    {
        $this->authenticate(['ROLE_USER']);

        $this->expectException(AccessDeniedException::class);
        $this->buildChecker()->check(new ServerRequest(), '/user/profile/456', []);
    }
}
