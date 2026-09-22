<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<ImportSummary>
 */
final readonly class ImportSummaryList extends AbstractObjectList
{
    /**
     * @return class-string<ImportSummary>
     */
    public static function itemType(): string
    {
        return ImportSummary::class;
    }
}
