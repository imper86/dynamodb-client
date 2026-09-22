<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class SearchSchemaElement
{
    /**
     * @param non-empty-string $attributeName
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $attributeName,
        public SearchSchemaElementType $searchSchemaElementType,
    ) {
        Assert::stringNotEmpty($this->attributeName);
        Assert::maxLength($this->attributeName, 65535);
    }
}
