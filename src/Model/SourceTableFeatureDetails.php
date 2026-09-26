<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * The features that were enabled on the table when the backup was created - its indexes, stream, TTL
 * and encryption settings.
 */
final class SourceTableFeatureDetails
{
    public function __construct(
        public readonly ?GlobalSecondaryIndexInfoList $globalSecondaryIndexes = null,
        public readonly ?LocalSecondaryIndexInfoList $localSecondaryIndexes = null,
        #[SerializedName('SSEDescription')]
        public readonly ?SSEDescription $sseDescription = null,
        public readonly ?StreamSpecification $streamDescription = null,
        public readonly ?TimeToLiveDescription $timeToLiveDescription = null,
        public readonly ?VectorIndexInfoList $vectorIndexes = null,
    ) {}
}
