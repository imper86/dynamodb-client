<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Serializer\Normalizer;

use Imper86\DynamoDBClient\Model\ConsumedCapacity;
use Imper86\DynamoDBClient\Serializer\Normalizer\ScalarRejectingDenormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

/**
 * @internal
 */
#[CoversClass(ScalarRejectingDenormalizer::class)]
final class ScalarRejectingDenormalizerTest extends TestCase
{
    public function testClaimsAScalarWhereAnObjectIsExpected(): void
    {
        $denormalizer = new ScalarRejectingDenormalizer();

        self::assertTrue($denormalizer->supportsDenormalization('x', ConsumedCapacity::class));
        self::assertTrue($denormalizer->supportsDenormalization(1, ConsumedCapacity::class));
        self::assertTrue($denormalizer->supportsDenormalization(false, ConsumedCapacity::class));
    }

    public function testLeavesArraysAndNullToOtherDenormalizers(): void
    {
        $denormalizer = new ScalarRejectingDenormalizer();

        self::assertFalse($denormalizer->supportsDenormalization([], ConsumedCapacity::class));
        self::assertFalse($denormalizer->supportsDenormalization(null, ConsumedCapacity::class));
    }

    public function testLeavesTypesThatAreNotClassesToOtherDenormalizers(): void
    {
        self::assertFalse(new ScalarRejectingDenormalizer()->supportsDenormalization('x', 'string'));
    }

    /**
     * @throws NotNormalizableValueException
     */
    public function testRejectsTheScalarAtItsPath(): void
    {
        $this->expectException(NotNormalizableValueException::class);

        try {
            new ScalarRejectingDenormalizer()->denormalize(
                'x',
                ConsumedCapacity::class,
                'json',
                ['deserialization_path' => 'consumedCapacity'],
            );
        } catch (NotNormalizableValueException $exception) {
            self::assertSame('consumedCapacity', $exception->getPath());
            self::assertSame(['array'], $exception->getExpectedTypes());
            self::assertSame('string', $exception->getCurrentType());

            throw $exception;
        }
    }
}
