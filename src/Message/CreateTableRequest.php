<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeDefinitionList;
use Imper86\DynamoDBClient\Model\BillingMode;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndexList;
use Imper86\DynamoDBClient\Model\GlobalTableSettingsReplicationMode;
use Imper86\DynamoDBClient\Model\KeySchemaElementList;
use Imper86\DynamoDBClient\Model\LocalSecondaryIndexList;
use Imper86\DynamoDBClient\Model\OnDemandThroughput;
use Imper86\DynamoDBClient\Model\ProvisionedThroughput;
use Imper86\DynamoDBClient\Model\SSESpecification;
use Imper86\DynamoDBClient\Model\StreamSpecification;
use Imper86\DynamoDBClient\Model\TableClass;
use Imper86\DynamoDBClient\Model\TagList;
use Imper86\DynamoDBClient\Model\VectorIndexList;
use Imper86\DynamoDBClient\Model\WarmThroughput;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Webmozart\Assert\Assert;

final class CreateTableRequest
{
    /**
     * Only the table name is required: a table created from `GlobalTableSourceArn` takes its key schema,
     * its attributes and its indexes from the source table instead of from this request.
     *
     * @param non-empty-string $tableName the name of the table to create, or the ARN of the table
     * @param null|non-empty-string $globalTableSourceArn
     * @param null|non-empty-string $resourcePolicy the resource-based policy document to attach, as JSON
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $tableName,
        public readonly ?AttributeDefinitionList $attributeDefinitions = null,
        public readonly ?BillingMode $billingMode = null,
        public readonly ?bool $deletionProtectionEnabled = null,
        public readonly ?GlobalSecondaryIndexList $globalSecondaryIndexes = null,
        public readonly ?GlobalTableSettingsReplicationMode $globalTableSettingsReplicationMode = null,
        public readonly ?string $globalTableSourceArn = null,
        public readonly ?KeySchemaElementList $keySchema = null,
        public readonly ?LocalSecondaryIndexList $localSecondaryIndexes = null,
        public readonly ?OnDemandThroughput $onDemandThroughput = null,
        public readonly ?ProvisionedThroughput $provisionedThroughput = null,
        public readonly ?string $resourcePolicy = null,
        #[SerializedName('SSESpecification')]
        public readonly ?SSESpecification $sseSpecification = null,
        public readonly ?StreamSpecification $streamSpecification = null,
        public readonly ?TableClass $tableClass = null,
        public readonly ?TagList $tags = null,
        public readonly ?VectorIndexList $vectorIndexes = null,
        public readonly ?WarmThroughput $warmThroughput = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrStringNotEmpty($this->globalTableSourceArn);
        Assert::nullOrMaxLength($this->globalTableSourceArn, 1024);
        Assert::nullOrMinCount($this->keySchema, 1);
        Assert::nullOrStringNotEmpty($this->resourcePolicy);
    }
}
