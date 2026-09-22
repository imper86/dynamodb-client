<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum BackupStatus: string
{
    case AVAILABLE = 'AVAILABLE';
    case CREATING = 'CREATING';
    case DELETED = 'DELETED';
}
