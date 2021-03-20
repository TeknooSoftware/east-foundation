<?php
/**
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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\FoundationBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;
use Teknoo\East\FoundationBundle\Normalizer\EastNormalizer;

/**
 * Class KernelListenerTest.
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\FoundationBundle\Normalizer\EastNormalizer
 */
class EastNormalizerTest extends \PHPUnit\Framework\TestCase
{
    public function buildNormalizer(): EastNormalizer
    {
        return new EastNormalizer();
    }

    public function testInjectDataBadArray()
    {
        $this->expectException(\TypeError::class);
        $this->buildNormalizer()->injectData(new \stdClass());
    }

    public function testInjectData()
    {
        self::assertInstanceOf(
            EastNormalizer::class,
            $this->buildNormalizer()->injectData(['foo' => 'bar'])
        );
    }

    public function testSupportsNormalization()
    {
        self::assertFalse($this->buildNormalizer()->supportsNormalization(new \stdClass()));
        self::assertTrue($this->buildNormalizer()->supportsNormalization(
            $this->createMock(NormalizableInterface::class)
        ));
    }

    public function testNormalizeBadObject()
    {
        $this->expectException(\RuntimeException::class);
        $this->buildNormalizer()->normalize(new \stdClass());
    }

    public function testNormalize()
    {
        $object = $this->createMock(NormalizableInterface::class);
        $normalizer = $this->buildNormalizer();

        $returnValue = ['foo' => 'bar'];
        $context = ['context' => 'hello'];

        $object->expects(self::once())
            ->method('exportToMeData')
            ->willReturnCallback(function ($nrmlz, $ctxt) use ($normalizer, $object, $context, $returnValue) {
                self::assertInstanceOf(EastNormalizer::class, $nrmlz);
                self::assertNotSame($normalizer, $nrmlz);
                self::assertEquals($context, $ctxt);

                $nrmlz->injectData($returnValue);

                return $object;
            });

        self::assertEquals(
            $returnValue,
            $normalizer->normalize($object, 'json', $context)
        );
    }

    public function testNormalizeWithAwareNormalizerWithOnlyScalarValues()
    {
        $object = $this->createMock(NormalizableInterface::class);
        $normalizer = $this->buildNormalizer();

        $returnValue = ['foo' => 'bar'];
        $context = ['context' => 'hello'];

        $object->expects(self::once())
            ->method('exportToMeData')
            ->willReturnCallback(function ($nrmlz, $ctxt) use ($normalizer, $object, $context, $returnValue) {
                self::assertInstanceOf(EastNormalizer::class, $nrmlz);
                self::assertNotSame($normalizer, $nrmlz);
                self::assertEquals($context, $ctxt);

                $nrmlz->injectData($returnValue);

                return $object;
            });

        $normalizer2 = $this->createMock(NormalizerInterface::class);
        $normalizer2->expects(self::never())
            ->method('normalize');

        $normalizer->setNormalizer($normalizer2);

        self::assertEquals(
            $returnValue,
            $normalizer->normalize($object, 'json', $context)
        );
    }

    public function testNormalizeWithAwareNormalizer()
    {
        $object = $this->createMock(NormalizableInterface::class);
        $normalizer = $this->buildNormalizer();

        $returnValue = ['foo' => 'bar', 'bar' => ($date = new \DateTime('2018-05-01 02:03:04'))];
        $returnValue2 = ['foo' => 'bar', 'bar' => '2018-05-01 02:03:04'];
        $context = ['context' => 'hello'];

        $object->expects(self::once())
            ->method('exportToMeData')
            ->willReturnCallback(function ($nrmlz, $ctxt) use ($normalizer, $object, $context, $returnValue) {
                self::assertInstanceOf(EastNormalizer::class, $nrmlz);
                self::assertNotSame($normalizer, $nrmlz);
                self::assertEquals($context, $ctxt);

                $nrmlz->injectData($returnValue);

                return $object;
            });

        $normalizer2 = $this->createMock(NormalizerInterface::class);
        $normalizer2->expects(self::once())
            ->method('normalize')
            ->with($date, 'json', $context)
            ->willReturn('2018-05-01 02:03:04');

        $normalizer->setNormalizer($normalizer2);

        self::assertEquals(
            $returnValue2,
            $normalizer->normalize($object, 'json', $context)
        );
    }
}
