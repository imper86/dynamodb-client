<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Serializer\NameConverter;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

final class PascalCaseNameConverter implements NameConverterInterface
{
    public function normalize(
        string $propertyName,
        ?string $class = null,
        ?string $format = null,
        array $context = [],
    ): string {
        return ucfirst($propertyName);
    }

    public function denormalize(
        string $propertyName,
        ?string $class = null,
        ?string $format = null,
        array $context = [],
    ): string {
        return lcfirst($propertyName);
    }
}
