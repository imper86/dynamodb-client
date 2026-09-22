<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<ParameterizedStatement>
 */
final readonly class ParameterizedStatementList extends AbstractObjectList
{
    /**
     * @return class-string<ParameterizedStatement>
     */
    public static function itemType(): string
    {
        return ParameterizedStatement::class;
    }
}
