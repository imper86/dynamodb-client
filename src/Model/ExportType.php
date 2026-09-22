<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum ExportType: string
{
    case FULL_EXPORT = 'FULL_EXPORT';
    case INCREMENTAL_EXPORT = 'INCREMENTAL_EXPORT';
}
