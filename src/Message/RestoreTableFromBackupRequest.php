<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\BillingMode;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndexList;
use Imper86\DynamoDBClient\Model\LocalSecondaryIndexList;
use Imper86\DynamoDBClient\Model\OnDemandThroughput;
use Imper86\DynamoDBClient\Model\ProvisionedThroughput;
use Imper86\DynamoDBClient\Model\SSESpecification;
use Imper86\DynamoDBClient\Model\VectorIndexList;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Webmozart\Assert\Assert;

final readonly class RestoreTableFromBackupRequest
{
    /**
     * The overrides replace what the backup holds: an index left out of an index override is not restored,
     * while a vector index override left out altogether restores every vector index of the backup.
     *
     * @param non-empty-string $backupArn
     * @param non-empty-string $targetTableName the name of the new table; it cannot be an ARN
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $backupArn,
        public string $targetTableName,
        public ?BillingMode $billingModeOverride = null,
        public ?GlobalSecondaryIndexList $globalSecondaryIndexOverride = null,
        public ?LocalSecondaryIndexList $localSecondaryIndexOverride = null,
        public ?OnDemandThroughput $onDemandThroughputOverride = null,
        public ?ProvisionedThroughput $provisionedThroughputOverride = null,
        #[SerializedName('SSESpecificationOverride')]
        public ?SSESpecification $sseSpecificationOverride = null,
        public ?VectorIndexList $vectorIndexOverride = null,
    ) {
        Assert::stringNotEmpty($this->backupArn);
        Assert::minLength($this->backupArn, 37);
        Assert::maxLength($this->backupArn, 1024);
        Assert::stringNotEmpty($this->targetTableName);
        Assert::minLength($this->targetTableName, 3);
        Assert::maxLength($this->targetTableName, 255);
        Assert::regex($this->targetTableName, '/^[a-zA-Z0-9_.\-]+$/');
    }
}
