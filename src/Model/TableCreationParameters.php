<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Webmozart\Assert\Assert;

final class TableCreationParameters
{
    /**
     * @param non-empty-string $tableName the name of the table to create; an ARN is not accepted here
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly AttributeDefinitionList $attributeDefinitions,
        public readonly KeySchemaElementList $keySchema,
        public readonly string $tableName,
        public readonly ?BillingMode $billingMode = null,
        public readonly ?GlobalSecondaryIndexList $globalSecondaryIndexes = null,
        public readonly ?OnDemandThroughput $onDemandThroughput = null,
        public readonly ?ProvisionedThroughput $provisionedThroughput = null,
        #[SerializedName('SSESpecification')]
        public readonly ?SSESpecification $sseSpecification = null,
        public readonly ?VectorIndexList $vectorIndexes = null,
    ) {
        Assert::minCount($this->keySchema, 1);
        Assert::stringNotEmpty($this->tableName);
        Assert::minLength($this->tableName, 3);
        Assert::maxLength($this->tableName, 255);
        Assert::regex($this->tableName, '/^[a-zA-Z0-9_.\-]+$/');
    }
}
