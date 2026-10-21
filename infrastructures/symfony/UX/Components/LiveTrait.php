<?php

/*
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

namespace Teknoo\East\FoundationBundle\UX\Components;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

/**
 * Trait to help to create component usable with East Recipe. The prop `originalPath` keeps the path (without front
 * controller) and the query string of the request displaying the component : East Foundation's router re-runs the
 * recipe of this path, with these query parameters, for a Live Component request.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait LiveTrait
{
    #[LiveProp]
    public ?string $originalPath = null;

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
        $this->updateOriginalPath();
    }

    private function updateOriginalPath(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $this->originalPath = $request?->getPathInfo();

        $queryString = $request?->getQueryString();
        if (null !== $queryString && '' !== $queryString) {
            $this->originalPath .= '?' . $queryString;
        }
    }
}
