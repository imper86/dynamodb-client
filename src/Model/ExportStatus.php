<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum ExportStatus: string
{
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';
    case IN_PROGRESS = 'IN_PROGRESS';
}
