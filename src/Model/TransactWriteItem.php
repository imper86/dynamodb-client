<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Webmozart\Assert\Assert;

use function array_filter;
use function count;

final readonly class TransactWriteItem
{
    /**
     * Exactly one of the four actions must be given; each action on an item needs its own TransactWriteItem.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        public ?ConditionCheck $conditionCheck = null,
        public ?Delete $delete = null,
        public ?Put $put = null,
        public ?Update $update = null,
    ) {
        Assert::same(
            count(array_filter(
                [$this->conditionCheck, $this->delete, $this->put, $this->update],
                static fn(?object $action): bool => null !== $action,
            )),
            1,
            'A TransactWriteItem needs exactly one of ConditionCheck, Delete, Put or Update.',
        );
    }

    /**
     * @param non-empty-string $conditionExpression the condition the item must meet for the transaction to succeed
     * @param non-empty-string $tableName the table name or its ARN
     * @throws InvalidArgumentException
     */
    public static function conditionCheck(
        string $conditionExpression,
        AttributeValueMap $key,
        string $tableName,
        ?NonEmptyStringMap $expressionAttributeNames = null,
        ?AttributeValueMap $expressionAttributeValues = null,
        ?ReturnValuesOnConditionCheckFailure $returnValuesOnConditionCheckFailure = null,
    ): self {
        return new self(conditionCheck: new ConditionCheck(
            conditionExpression: $conditionExpression,
            key: $key,
            tableName: $tableName,
            expressionAttributeNames: $expressionAttributeNames,
            expressionAttributeValues: $expressionAttributeValues,
            returnValuesOnConditionCheckFailure: $returnValuesOnConditionCheckFailure,
        ));
    }

    /**
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|non-empty-string $conditionExpression
     * @throws InvalidArgumentException
     */
    public static function delete(
        AttributeValueMap $key,
        string $tableName,
        ?string $conditionExpression = null,
        ?NonEmptyStringMap $expressionAttributeNames = null,
        ?AttributeValueMap $expressionAttributeValues = null,
        ?ReturnValuesOnConditionCheckFailure $returnValuesOnConditionCheckFailure = null,
    ): self {
        return new self(delete: new Delete(
            key: $key,
            tableName: $tableName,
            conditionExpression: $conditionExpression,
            expressionAttributeNames: $expressionAttributeNames,
            expressionAttributeValues: $expressionAttributeValues,
            returnValuesOnConditionCheckFailure: $returnValuesOnConditionCheckFailure,
        ));
    }

    /**
     * @param AttributeValueMap $item the whole item, primary key attributes included
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|non-empty-string $conditionExpression
     * @throws InvalidArgumentException
     */
    public static function put(
        AttributeValueMap $item,
        string $tableName,
        ?string $conditionExpression = null,
        ?NonEmptyStringMap $expressionAttributeNames = null,
        ?AttributeValueMap $expressionAttributeValues = null,
        ?ReturnValuesOnConditionCheckFailure $returnValuesOnConditionCheckFailure = null,
    ): self {
        return new self(put: new Put(
            item: $item,
            tableName: $tableName,
            conditionExpression: $conditionExpression,
            expressionAttributeNames: $expressionAttributeNames,
            expressionAttributeValues: $expressionAttributeValues,
            returnValuesOnConditionCheckFailure: $returnValuesOnConditionCheckFailure,
        ));
    }

    /**
     * @param non-empty-string $tableName the table name or its ARN
     * @param non-empty-string $updateExpression the attributes to change, and how
     * @param null|non-empty-string $conditionExpression
     * @throws InvalidArgumentException
     */
    public static function update(
        AttributeValueMap $key,
        string $tableName,
        string $updateExpression,
        ?string $conditionExpression = null,
        ?NonEmptyStringMap $expressionAttributeNames = null,
        ?AttributeValueMap $expressionAttributeValues = null,
        ?ReturnValuesOnConditionCheckFailure $returnValuesOnConditionCheckFailure = null,
    ): self {
        return new self(update: new Update(
            key: $key,
            tableName: $tableName,
            updateExpression: $updateExpression,
            conditionExpression: $conditionExpression,
            expressionAttributeNames: $expressionAttributeNames,
            expressionAttributeValues: $expressionAttributeValues,
            returnValuesOnConditionCheckFailure: $returnValuesOnConditionCheckFailure,
        ));
    }
}
