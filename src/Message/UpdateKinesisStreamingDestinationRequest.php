<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\ApproximateCreationDateTimePrecision;
use Imper86\DynamoDBClient\Model\UpdateKinesisStreamingConfiguration;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class UpdateKinesisStreamingDestinationRequest
{
    /**
     * @param non-empty-string $streamArn the ARN of the Kinesis data stream the table streams to
     * @param non-empty-string $tableName the table name or its ARN
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $streamArn,
        public string $tableName,
        public ?UpdateKinesisStreamingConfiguration $updateKinesisStreamingConfiguration = null,
    ) {
        Assert::stringNotEmpty($this->streamArn);
        Assert::minLength($this->streamArn, 37);
        Assert::maxLength($this->streamArn, 1024);
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
    }

    /**
     * Changes the precision of the timestamp on each record the table puts on the stream.
     *
     * @param non-empty-string $streamArn the ARN of the Kinesis data stream the table streams to
     * @param non-empty-string $tableName the table name or its ARN
     * @throws InvalidArgumentException
     */
    public static function precision(
        string $streamArn,
        string $tableName,
        ApproximateCreationDateTimePrecision $approximateCreationDateTimePrecision,
    ): self {
        return new self(
            streamArn: $streamArn,
            tableName: $tableName,
            updateKinesisStreamingConfiguration: new UpdateKinesisStreamingConfiguration(
                $approximateCreationDateTimePrecision,
            ),
        );
    }
}
