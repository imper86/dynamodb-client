<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class DescribeKinesisStreamingDestinationRequest
{
    /**
     * @param non-empty-string $tableName the table name or its ARN
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $tableName,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
    }
}
