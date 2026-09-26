<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<BackupSummary>
 */
final class BackupSummaryList extends AbstractObjectList
{
    /**
     * @return class-string<BackupSummary>
     */
    public static function itemType(): string
    {
        return BackupSummary::class;
    }
}
