<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient\Serializer;

use OoAws\DynamoDBClient\Serializer\NameConverter\PascalCaseNameConverter;
use OoAws\DynamoDBClient\Serializer\Normalizer\CollectionNormalizer;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

final readonly class SerializerFactory
{
    public static function create(): Serializer
    {
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $nameConverter = new MetadataAwareNameConverter(
            $classMetadataFactory,
            new PascalCaseNameConverter(),
        );
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        return new Serializer(
            [
                new CollectionNormalizer(),
                new BackedEnumNormalizer(),
                new PropertyNormalizer(
                    $classMetadataFactory,
                    $nameConverter,
                    new PropertyInfoExtractor(
                        [$reflectionExtractor],
                        [$phpDocExtractor, $reflectionExtractor],
                        [$phpDocExtractor],
                        [$reflectionExtractor],
                        [$reflectionExtractor],
                    ),
                    new ClassDiscriminatorFromClassMetadata($classMetadataFactory),
                    null,
                    [
                        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                    ],
                ),
            ],
            [new JsonEncoder()],
        );
    }
}
