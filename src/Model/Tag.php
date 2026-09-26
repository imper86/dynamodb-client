<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class Tag
{
    /**
     * @param non-empty-string $key
     * @param string $value the tag value, which may be empty
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $key,
        public readonly string $value,
    ) {
        Assert::stringNotEmpty($this->key);
        Assert::maxLength($this->key, 128);
        Assert::maxLength($this->value, 256);
    }
}
