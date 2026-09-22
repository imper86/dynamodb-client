<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Serializer\Normalizer;

use DateRangeError;
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

        try {
            return DateTimeImmutable::createFromTimestamp($data);
        } catch (DateRangeError $e) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                $e->getMessage(),
                $data,
                ['int', 'float'],
                $this->path($context),
                true,
                0,
                $e,
            );
        }
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
     * @param array<string, mixed> $context
     */
    private function path(array $context): ?string
    {
        $path = $context['deserialization_path'] ?? null;

        return is_string($path) ? $path : null;
    }
}
