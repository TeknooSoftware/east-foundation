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
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Twig\Template;

use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Throwable;
use Twig\Environment;

/**
 * Twig adapter to use into East context, implementing the `EngineInterface`.
 * The template is rendered only when the result is converted to string
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Engine implements EngineInterface
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    /**
     * @param PromiseInterface<mixed, mixed> $promise
     */
    public function render(PromiseInterface $promise, string $view, array $parameters = []): EngineInterface
    {
        try {
            $promise->success(
                new class ($this->twig, $view, $parameters) implements ResultInterface {
                    /**
                     * @param array<string, mixed> $parameters
                     */
                    public function __construct(
                        private readonly Environment $twig,
                        private readonly string $view,
                        private readonly array $parameters,
                    ) {
                    }

                    public function __toString(): string
                    {
                        return $this->twig->render($this->view, $this->parameters);
                    }
                }
            );
        } catch (Throwable $throwable) {
            $promise->fail($throwable);
        }

        return $this;
    }
}
