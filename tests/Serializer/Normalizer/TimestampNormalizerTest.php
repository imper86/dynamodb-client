<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Serializer\Normalizer;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Imper86\DynamoDBClient\Serializer\Normalizer\TimestampNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Serializer;

/**
 * @internal
 */
#[CoversClass(TimestampNormalizer::class)]
final class TimestampNormalizerTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new Serializer([new TimestampNormalizer()], [new JsonEncoder()]);
    }

    /**
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function testSerializesToEpochSecondsAsAJsonNumber(): void
    {
        $dateTime = new DateTimeImmutable('2019-12-17T23:07:46.799+00:00');

        self::assertSame('1576624066.799', $this->serializer->serialize($dateTime, 'json'));
    }

    /**
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function testSerializesTheSameInstantWhateverTheTimezone(): void
    {
        $dateTime = new DateTime('2019-12-18T00:07:46.799', new DateTimeZone('Europe/Warsaw'));

        self::assertSame('1576624066.799', $this->serializer->serialize($dateTime, 'json'));
    }

    /**
     * @throws ExceptionInterface
     */
    public function testDeserializesFractionalEpochSecondsInUtc(): void
    {
        $dateTime = $this->serializer->deserialize('1576624066.799', DateTimeImmutable::class, 'json');

        self::assertInstanceOf(DateTimeImmutable::class, $dateTime);
        self::assertSame('2019-12-17T23:07:46.799000+00:00', $dateTime->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * @throws ExceptionInterface
     */
    public function testDeserializesWholeEpochSeconds(): void
    {
        $dateTime = $this->serializer->deserialize('1579734000', DateTimeImmutable::class, 'json');

        self::assertInstanceOf(DateTimeImmutable::class, $dateTime);
        self::assertSame('2020-01-22T23:00:00.000000+00:00', $dateTime->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * @throws ExceptionInterface
     */
    public function testDeserializesTheInterfaceToAnImmutableDateTime(): void
    {
        $dateTime = $this->serializer->deserialize('1579734000', DateTimeInterface::class, 'json');

        self::assertInstanceOf(DateTimeImmutable::class, $dateTime);
    }

    /**
     * @throws ExceptionInterface
     */
    public function testRejectsATimestampThatIsNotANumber(): void
    {
        $this->expectException(NotNormalizableValueException::class);
        $this->expectExceptionMessageIsOrContains('A timestamp must be a number of epoch seconds, "string" given.');

        $this->serializer->deserialize('"2019-12-17T23:07:46Z"', DateTimeImmutable::class, 'json');
    }

    /**
     * @throws ExceptionInterface
     */
    public function testRejectsATimestampOutsideTheRangeOfADateTime(): void
    {
        $this->expectException(NotNormalizableValueException::class);

        $this->serializer->deserialize('1e30', DateTimeImmutable::class, 'json');
    }

    /**
     * @throws ExceptionInterface
     */
    public function testRefusesToNormalizeAnythingButADateTime(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TimestampNormalizer()->normalize('2019-12-17');
    }

    public function testLeavesMutableDateTimeToOtherDenormalizers(): void
    {
        self::assertFalse(new TimestampNormalizer()->supportsDenormalization(1579734000, DateTime::class));
    }
}
