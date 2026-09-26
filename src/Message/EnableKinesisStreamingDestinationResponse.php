<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\DestinationStatus;
use Imper86\DynamoDBClient\Model\EnableKinesisStreamingConfiguration;

final class EnableKinesisStreamingDestinationResponse
{
    public function __construct(
        public readonly ?DestinationStatus $destinationStatus = null,
        public readonly ?EnableKinesisStreamingConfiguration $enableKinesisStreamingConfiguration = null,
        public readonly ?string $streamArn = null,
        public readonly ?string $tableName = null,
    ) {}
}
