<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<BatchStatementRequest>
 */
final readonly class BatchStatementRequestList extends AbstractObjectList
{
    /**
     * @return class-string<BatchStatementRequest>
     */
    public static function itemType(): string
    {
        return BatchStatementRequest::class;
    }
}
