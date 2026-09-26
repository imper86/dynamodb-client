<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\DestinationStatus;
use Imper86\DynamoDBClient\Model\UpdateKinesisStreamingConfiguration;

final class UpdateKinesisStreamingDestinationResponse
{
    /**
     * @param null|DestinationStatus $destinationStatus typically `UPDATING` while the change takes effect
     */
    public function __construct(
        public readonly ?DestinationStatus $destinationStatus = null,
        public readonly ?string $streamArn = null,
        public readonly ?string $tableName = null,
        public readonly ?UpdateKinesisStreamingConfiguration $updateKinesisStreamingConfiguration = null,
    ) {}
}
