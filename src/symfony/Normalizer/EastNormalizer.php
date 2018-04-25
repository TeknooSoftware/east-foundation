<?php

declare(strict_types=1);

/**
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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\FoundationBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;

/**
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class EastNormalizer implements EastNormalizerInterface, NormalizerInterface
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @inheritDoc
     */
    public function injectData(array $data): EastNormalizerInterface
    {
        $this->data = \array_merge($this->data, $data);

        return $this;
    }

    /**
     * @return EastNormalizer
     */
    private function cleanData(): self
    {
        $this->data = [];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if (!$object instanceof NormalizableInterface) {
            throw new \RuntimeException(
                \sprintf(
                    'Error the class "%s" does not implement the interface "%s"',
                    \get_class($object),
                    NormalizableInterface::class
                )
            );
        }

        $that = clone $this;
        $that->cleanData();

        $object->exportToMeData($that, $context);

        return $that->data;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof NormalizableInterface;
    }
}
