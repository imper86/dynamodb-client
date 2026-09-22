<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum BackupTypeFilter: string
{
    case ALL = 'ALL';
    case AWS_BACKUP = 'AWS_BACKUP';
    case SYSTEM = 'SYSTEM';
    case USER = 'USER';
}
