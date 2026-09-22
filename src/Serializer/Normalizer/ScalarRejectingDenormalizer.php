<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Serializer\Normalizer;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

use function class_exists;
use function get_debug_type;
use function is_scalar;
use function is_string;
use function sprintf;

/**
 * Rejects a scalar where the response should hold an object. {@see PropertyNormalizer} would cast it to
 * an array instead, find none of the constructor's parameters in it, and build the object from its
 * defaults, so `{"ConsumedCapacity":"x"}` would arrive as an empty ConsumedCapacity.
 *
 * It must come after every denormalizer that turns a scalar into an object (enums, timestamps) and
 * before PropertyNormalizer. Null passes through, so a member sent as null reads as if it were absent.
 */
final class ScalarRejectingDenormalizer implements DenormalizerInterface
{
    /**
     * @param array<string, mixed> $context
     * @throws NotNormalizableValueException
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): never
    {
        $path = $context['deserialization_path'] ?? null;

        throw NotNormalizableValueException::createForUnexpectedDataType(
            sprintf('Data expected to be an object of type "%s", "%s" given.', $type, get_debug_type($data)),
            $data,
            ['array'],
            is_string($path) ? $path : null,
            true,
        );
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
        return is_scalar($data) && class_exists($type);
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['object' => false];
    }
}
