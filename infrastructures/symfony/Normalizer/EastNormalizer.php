<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\FoundationBundle\Normalizer;

use SensitiveParameter;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;
use Teknoo\East\FoundationBundle\Normalizer\Exception\NotNormalizableException;

use function is_object;
use function is_scalar;
use function sprintf;

/**
 * Symfony normalizer to allow serialization of object following the East pattern, and implementing the interface
 * `Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface`.
 * This normalizer is not able to extract directly values from normalizable object, it ask each object to pass to him
 * exportable data.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class EastNormalizer implements EastNormalizerInterface, NormalizerInterface, NormalizerAwareInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    private ?NormalizerInterface $normalizer = null;

    public function setNormalizer(NormalizerInterface $normalizer): self
    {
        $this->normalizer = $normalizer;

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function injectData(array $data): EastNormalizerInterface
    {
        $this->data = $data + $this->data;

        return $this;
    }

    private function cleanData(): self
    {
        $this->data = [];

        return $this;
    }

    /**
     * @param array<string, string[]> $context
     * @return array<int|string, mixed>
     */
    public function normalize(
        #[SensitiveParameter] mixed $object,
        ?string $format = null,
        array $context = [],
    ): array {
        if (!$object instanceof NormalizableInterface) {
            throw new NotNormalizableException(
                sprintf(
                    'Error the class "%s" does not implement the interface "%s"',
                    is_object($object) ? $object::class : 'scalar',
                    NormalizableInterface::class
                )
            );
        }

        $that = clone $this;
        $that->cleanData();

        $object->exportToMeData($that, $context);

        if (!$this->normalizer instanceof NormalizerInterface) {
            return $that->data;
        }

        foreach ($that->data as &$item) {
            if (!is_scalar($item)) {
                $item = $this->normalizer->normalize($item, $format, $context);
            }
        }

        return $that->data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(
        #[SensitiveParameter] mixed $data,
        ?string $format = null,
        array $context = [],
    ): bool {
        return $data instanceof NormalizableInterface;
    }

    /**
     * @return array<class-string|'*'|'object'|string, bool|null>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            NormalizableInterface::class => false,
        ];
    }
}
