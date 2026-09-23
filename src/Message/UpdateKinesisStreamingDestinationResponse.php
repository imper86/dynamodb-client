<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\DestinationStatus;
use Imper86\DynamoDBClient\Model\UpdateKinesisStreamingConfiguration;

final readonly class UpdateKinesisStreamingDestinationResponse
{
    /**
     * @param null|DestinationStatus $destinationStatus typically `UPDATING` while the change takes effect
     */
    public function __construct(
        public ?DestinationStatus $destinationStatus = null,
        public ?string $streamArn = null,
        public ?string $tableName = null,
        public ?UpdateKinesisStreamingConfiguration $updateKinesisStreamingConfiguration = null,
    ) {}
}
