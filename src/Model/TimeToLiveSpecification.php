<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class TimeToLiveSpecification
{
    /**
     * @param non-empty-string $attributeName the attribute that holds each item's expiry, in epoch seconds
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $attributeName,
        public bool $enabled,
    ) {
        Assert::stringNotEmpty($this->attributeName);
        Assert::maxLength($this->attributeName, 255);
    }
}
