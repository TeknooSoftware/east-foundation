<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
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

namespace Teknoo\East\FoundationBundle\EndPoint;

use Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Trait to help developer to write endpoint with Symfony (also called controller) and reuse Symfony components
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait ExceptionTrait
{
    /*
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     *     throw $this->createNotFoundException('Page not found!');
     */
    protected function createNotFoundException(
        string $message = 'Not Found',
        ?Exception $previous = null
    ): NotFoundHttpException {
        return new NotFoundHttpException($message, $previous);
    }

    /*
     * Returns an AccessDeniedException.
     *
     * This will result in a 403 response code. Usage example:
     *
     *     throw $this->createAccessDeniedException('Unable to access this page!');
     */
    protected function createAccessDeniedException(
        string $message = 'Access Denied.',
        ?Exception $previous = null
    ): AccessDeniedHttpException {
        return new AccessDeniedHttpException($message, $previous);
    }
}
