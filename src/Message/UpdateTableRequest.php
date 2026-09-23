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

final readonly class UpdateTableRequest
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
        public string $tableName,
        public ?AttributeDefinitionList $attributeDefinitions = null,
        public ?BillingMode $billingMode = null,
        public ?bool $deletionProtectionEnabled = null,
        public ?GlobalSecondaryIndexUpdateList $globalSecondaryIndexUpdates = null,
        public ?GlobalTableSettingsReplicationMode $globalTableSettingsReplicationMode = null,
        public ?GlobalTableWitnessGroupUpdateList $globalTableWitnessUpdates = null,
        public ?MultiRegionConsistency $multiRegionConsistency = null,
        public ?OnDemandThroughput $onDemandThroughput = null,
        public ?ProvisionedThroughput $provisionedThroughput = null,
        public ?ReplicationGroupUpdateList $replicaUpdates = null,
        #[SerializedName('SSESpecification')]
        public ?SSESpecification $sseSpecification = null,
        public ?StreamSpecification $streamSpecification = null,
        public ?TableClass $tableClass = null,
        public ?VectorIndexUpdateList $vectorIndexUpdates = null,
        public ?WarmThroughput $warmThroughput = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrCount($this->globalTableWitnessUpdates, 1);
        Assert::nullOrMinCount($this->replicaUpdates, 1);
    }
}
