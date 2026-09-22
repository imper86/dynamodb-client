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

final readonly class CreateTableRequest
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
        public string $tableName,
        public ?AttributeDefinitionList $attributeDefinitions = null,
        public ?BillingMode $billingMode = null,
        public ?bool $deletionProtectionEnabled = null,
        public ?GlobalSecondaryIndexList $globalSecondaryIndexes = null,
        public ?GlobalTableSettingsReplicationMode $globalTableSettingsReplicationMode = null,
        public ?string $globalTableSourceArn = null,
        public ?KeySchemaElementList $keySchema = null,
        public ?LocalSecondaryIndexList $localSecondaryIndexes = null,
        public ?OnDemandThroughput $onDemandThroughput = null,
        public ?ProvisionedThroughput $provisionedThroughput = null,
        public ?string $resourcePolicy = null,
        #[SerializedName('SSESpecification')]
        public ?SSESpecification $sseSpecification = null,
        public ?StreamSpecification $streamSpecification = null,
        public ?TableClass $tableClass = null,
        public ?TagList $tags = null,
        public ?VectorIndexList $vectorIndexes = null,
        public ?WarmThroughput $warmThroughput = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrStringNotEmpty($this->globalTableSourceArn);
        Assert::nullOrMaxLength($this->globalTableSourceArn, 1024);
        Assert::nullOrMinCount($this->keySchema, 1);
        Assert::nullOrStringNotEmpty($this->resourcePolicy);
    }
}
