<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\EnableKinesisStreamingConfiguration;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class EnableKinesisStreamingDestinationRequest
{
    /**
     * @param non-empty-string $streamArn the ARN of the Kinesis data stream to start streaming to
     * @param non-empty-string $tableName the table name or its ARN
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $streamArn,
        public readonly string $tableName,
        public readonly ?EnableKinesisStreamingConfiguration $enableKinesisStreamingConfiguration = null,
    ) {
        Assert::stringNotEmpty($this->streamArn);
        Assert::minLength($this->streamArn, 37);
        Assert::maxLength($this->streamArn, 1024);
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
    }
}
