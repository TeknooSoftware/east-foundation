<?php
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

namespace Teknoo\Tests\East\FoundationBundle\Normalizer;

use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;
use Teknoo\East\FoundationBundle\Normalizer\EastNormalizer;

/**
 * Class KernelListenerTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
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

    /**
     * @expectedException \TypeError
     */
    public function testInjectDataBadArray()
    {
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

    /**
     * @expectedException \RuntimeException
     */
    public function testNormalizeBadObject()
    {
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
}