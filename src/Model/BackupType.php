<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum BackupType: string
{
    case AWS_BACKUP = 'AWS_BACKUP';
    case SYSTEM = 'SYSTEM';
    case USER = 'USER';
}
