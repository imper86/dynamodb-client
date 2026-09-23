<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;

final readonly class TransactGetItem
{
    public function __construct(
        public Get $get,
    ) {}

    /**
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|non-empty-string $projectionExpression
     * @throws InvalidArgumentException
     */
    public static function get(
        AttributeValueMap $key,
        string $tableName,
        ?NonEmptyStringMap $expressionAttributeNames = null,
        ?string $projectionExpression = null,
    ): self {
        return new self(new Get(
            key: $key,
            tableName: $tableName,
            expressionAttributeNames: $expressionAttributeNames,
            projectionExpression: $projectionExpression,
        ));
    }
}
