<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * The features that were enabled on the table when the backup was created - its indexes, stream, TTL
 * and encryption settings.
 */
final readonly class SourceTableFeatureDetails
{
    public function __construct(
        public ?GlobalSecondaryIndexInfoList $globalSecondaryIndexes = null,
        public ?LocalSecondaryIndexInfoList $localSecondaryIndexes = null,
        #[SerializedName('SSEDescription')]
        public ?SSEDescription $sseDescription = null,
        public ?StreamSpecification $streamDescription = null,
        public ?TimeToLiveDescription $timeToLiveDescription = null,
        public ?VectorIndexInfoList $vectorIndexes = null,
    ) {}
}
