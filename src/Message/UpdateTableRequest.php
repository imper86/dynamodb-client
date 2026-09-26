<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeDefinitionList;
use Imper86\DynamoDBClient\Model\BillingMode;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndexUpdateList;
use Imper86\DynamoDBClient\Model\GlobalTableSettingsReplicationMode;
use Imper86\DynamoDBClient\Model\GlobalTableWitnessGroupUpdateList;
use Imper86\DynamoDBClient\Model\MultiRegionConsistency;
use Imper86\DynamoDBClient\Model\OnDemandThroughput;
use Imper86\DynamoDBClient\Model\ProvisionedThroughput;
use Imper86\DynamoDBClient\Model\ReplicationGroupUpdateList;
use Imper86\DynamoDBClient\Model\SSESpecification;
use Imper86\DynamoDBClient\Model\StreamSpecification;
use Imper86\DynamoDBClient\Model\TableClass;
use Imper86\DynamoDBClient\Model\VectorIndexUpdateList;
use Imper86\DynamoDBClient\Model\WarmThroughput;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Webmozart\Assert\Assert;

final class UpdateTableRequest
{
    /**
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|AttributeDefinitionList $attributeDefinitions must include the key attributes of an index
     *                                                           the request creates
     * @param null|GlobalTableWitnessGroupUpdateList $globalTableWitnessUpdates exactly one witness to create or
     *                                                                          delete
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $tableName,
        public readonly ?AttributeDefinitionList $attributeDefinitions = null,
        public readonly ?BillingMode $billingMode = null,
        public readonly ?bool $deletionProtectionEnabled = null,
        public readonly ?GlobalSecondaryIndexUpdateList $globalSecondaryIndexUpdates = null,
        public readonly ?GlobalTableSettingsReplicationMode $globalTableSettingsReplicationMode = null,
        public readonly ?GlobalTableWitnessGroupUpdateList $globalTableWitnessUpdates = null,
        public readonly ?MultiRegionConsistency $multiRegionConsistency = null,
        public readonly ?OnDemandThroughput $onDemandThroughput = null,
        public readonly ?ProvisionedThroughput $provisionedThroughput = null,
        public readonly ?ReplicationGroupUpdateList $replicaUpdates = null,
        #[SerializedName('SSESpecification')]
        public readonly ?SSESpecification $sseSpecification = null,
        public readonly ?StreamSpecification $streamSpecification = null,
        public readonly ?TableClass $tableClass = null,
        public readonly ?VectorIndexUpdateList $vectorIndexUpdates = null,
        public readonly ?WarmThroughput $warmThroughput = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrCount($this->globalTableWitnessUpdates, 1);
        Assert::nullOrMinCount($this->replicaUpdates, 1);
    }
}
