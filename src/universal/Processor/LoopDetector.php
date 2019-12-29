<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Processor;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\Recipe\Bowl\RecipeBowl;

/**
 * Invokable able to detect if all requests in the stack are processed or if the Processor
 * Recipe is ended.
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class LoopDetector implements LoopDetectorInterface
{
    public function __invoke(
        RecipeBowl $bowl,
        ManagerInterface $manager,
        ResultInterface $result = null
    ): LoopDetectorInterface {
        if ($result instanceof ResultInterface) {
            //To manage when there are not result in the initial request
            $result = $result->getNext();
        }

        if (!$result instanceof ResultInterface) {
            $bowl->stopLooping();
        }

        $manager->updateWorkPlan([ResultInterface::class => $result]);

        return $this;
    }
}
