<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Webmozart\Assert\Assert;

final readonly class TableCreationParameters
{
    /**
     * @param non-empty-string $tableName the name of the table to create; an ARN is not accepted here
     * @throws InvalidArgumentException
     */
    public function __construct(
        public AttributeDefinitionList $attributeDefinitions,
        public KeySchemaElementList $keySchema,
        public string $tableName,
        public ?BillingMode $billingMode = null,
        public ?GlobalSecondaryIndexList $globalSecondaryIndexes = null,
        public ?OnDemandThroughput $onDemandThroughput = null,
        public ?ProvisionedThroughput $provisionedThroughput = null,
        #[SerializedName('SSESpecification')]
        public ?SSESpecification $sseSpecification = null,
        public ?VectorIndexList $vectorIndexes = null,
    ) {
        Assert::minCount($this->keySchema, 1);
        Assert::stringNotEmpty($this->tableName);
        Assert::minLength($this->tableName, 3);
        Assert::maxLength($this->tableName, 255);
        Assert::regex($this->tableName, '/^[a-zA-Z0-9_.\-]+$/');
    }
}
