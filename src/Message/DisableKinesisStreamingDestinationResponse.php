<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\DestinationStatus;
use Imper86\DynamoDBClient\Model\EnableKinesisStreamingConfiguration;

final readonly class DisableKinesisStreamingDestinationResponse
{
    public function __construct(
        public ?DestinationStatus $destinationStatus = null,
        public ?EnableKinesisStreamingConfiguration $enableKinesisStreamingConfiguration = null,
        public ?string $streamArn = null,
        public ?string $tableName = null,
    ) {}
}
