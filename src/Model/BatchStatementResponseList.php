<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<BatchStatementResponse>
 */
final class BatchStatementResponseList extends AbstractObjectList
{
    /**
     * @return class-string<BatchStatementResponse>
     */
    public static function itemType(): string
    {
        return BatchStatementResponse::class;
    }
}
