<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum ContinuousBackupsStatus: string
{
    case ENABLED = 'ENABLED';
    case DISABLED = 'DISABLED';
}
