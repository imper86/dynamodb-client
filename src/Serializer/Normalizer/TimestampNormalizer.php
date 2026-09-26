<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Serializer\Normalizer;

use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException as SerializerInvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use function get_debug_type;
use function is_float;
use function is_int;
use function is_string;
use function sprintf;

/**
 * Converts between {@see DateTimeInterface} and an AWS `Timestamp`, which the JSON protocol puts on
 * the wire as a number of epoch seconds with a fractional part. Symfony's own DateTimeNormalizer
 * only learned to emit a number (CAST_KEY) in 7.1, so this keeps the format identical on every
 * supported Symfony version.
 *
 * Denormalized values are {@see DateTimeImmutable} in UTC.
 */
final class TimestampNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param array<string, mixed> $context
     * @throws SerializerInvalidArgumentException
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): float
    {
        if (!$data instanceof DateTimeInterface) {
            throw new SerializerInvalidArgumentException(
                sprintf('Expected an instance of "%s", "%s" given.', DateTimeInterface::class, get_debug_type($data)),
            );
        }

        return (float) $data->format('U.u');
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof DateTimeInterface;
    }

    /**
     * @param array<string, mixed> $context
     * @throws NotNormalizableValueException
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!is_int($data) && !is_float($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                sprintf('A timestamp must be a number of epoch seconds, "%s" given.', get_debug_type($data)),
                $data,
                ['int', 'float'],
                $this->path($context),
                true,
            );
        }

        $dateTime = $this->fromTimestamp($data);

        if (!$dateTime instanceof DateTimeImmutable) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                sprintf('A timestamp must be a finite number between %d and %d, %s given.', PHP_INT_MIN, PHP_INT_MAX, $data),
                $data,
                ['int', 'float'],
                $this->path($context),
                true,
            );
        }

        return $dateTime;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): bool {
        return DateTimeInterface::class === $type || DateTimeImmutable::class === $type;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            DateTimeInterface::class => true,
            DateTimeImmutable::class => true,
        ];
    }

    /**
     * What DateTimeImmutable::createFromTimestamp() does from PHP 8.4 on: the fraction is rounded to
     * microseconds and always counted forwards, so -1.5 is half a second after -2.
     */
    private function fromTimestamp(float|int $timestamp): ?DateTimeImmutable
    {
        if (is_int($timestamp)) {
            $seconds = $timestamp;
            $microseconds = 0;
        } else {
            $whole = floor($timestamp);

            // (float) PHP_INT_MAX rounds up to 2^63, which is already out of range.
            if (!is_finite($whole) || $whole < PHP_INT_MIN || $whole >= PHP_INT_MAX) {
                return null;
            }

            $seconds = (int) $whole;
            $microseconds = (int) round(($timestamp - $whole) * 1_000_000);

            if (1_000_000 === $microseconds) {
                ++$seconds;
                $microseconds = 0;
            }
        }

        // setTime() carries any number of seconds over into the date, so this counts from the epoch.
        return (new DateTimeImmutable('@0'))->setTime(0, 0, $seconds, $microseconds);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function path(array $context): ?string
    {
        $path = $context['deserialization_path'] ?? null;

        return is_string($path) ? $path : null;
    }
}
