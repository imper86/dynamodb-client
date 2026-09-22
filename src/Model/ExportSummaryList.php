<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<ExportSummary>
 */
final readonly class ExportSummaryList extends AbstractObjectList
{
    /**
     * @return class-string<ExportSummary>
     */
    public static function itemType(): string
    {
        return ExportSummary::class;
    }
}
