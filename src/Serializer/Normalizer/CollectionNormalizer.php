<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Serializer\Normalizer;

use ArrayObject;
use InvalidArgumentException;
use Imper86\DynamoDBClient\ValueObject\AbstractMap;
use Imper86\DynamoDBClient\ValueObject\CollectionInterface;
use Imper86\DynamoDBClient\ValueObject\ObjectCollectionInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException as SerializerInvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use function array_map;
use function get_debug_type;
use function is_a;
use function is_array;
use function is_string;
use function sprintf;

/**
 * Normalizes every {@see CollectionInterface} to a plain array (a JSON array for lists and sets,
 * a JSON object for maps) and denormalizes it back, delegating items of
 * {@see ObjectCollectionInterface} to the rest of the serializer chain.
 */
final class CollectionNormalizer implements
    NormalizerInterface,
    DenormalizerInterface,
    NormalizerAwareInterface,
    DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    /**
     * @param array<string, mixed> $context
     * @return array<array-key, mixed>|ArrayObject<string, never>
     * @throws ExceptionInterface
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|ArrayObject
    {
        if (!$data instanceof CollectionInterface) {
            throw new SerializerInvalidArgumentException(
                sprintf('Expected an instance of "%s", "%s" given.', CollectionInterface::class, get_debug_type($data)),
            );
        }

        // An empty map must stay a JSON object ({}), not become an empty JSON array ([]).
        if ($data instanceof AbstractMap && $data->isEmpty()) {
            return new ArrayObject();
        }

        if (!$data instanceof ObjectCollectionInterface) {
            return $data->toArray();
        }

        return array_map(
            fn(object $item): mixed => $this->normalizer->normalize($item, $format, $context),
            $data->toArray(),
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof CollectionInterface;
    }

    /**
     * @param array<string, mixed> $context
     * @throws ExceptionInterface
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!is_array($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                sprintf('Data expected to be an array, "%s" given.', get_debug_type($data)),
                $data,
                ['array'],
                $this->path($context),
                true,
            );
        }

        if (!is_a($type, CollectionInterface::class, true)) {
            throw new SerializerInvalidArgumentException(
                sprintf('Type "%s" is not a "%s".', $type, CollectionInterface::class),
            );
        }

        if (is_a($type, ObjectCollectionInterface::class, true)) {
            $itemType = $type::itemType();
            foreach ($data as $key => $item) {
                $data[$key] = $this->denormalizer->denormalize($item, $itemType, $format, $context);
            }
        }

        try {
            return new $type($data);
        } catch (InvalidArgumentException $e) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                $e->getMessage(),
                $data,
                [$type],
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
        return is_array($data) && is_a($type, CollectionInterface::class, true);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [CollectionInterface::class => true];
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
