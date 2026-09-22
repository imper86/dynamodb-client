<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum PointInTimeRecoveryStatus: string
{
    case ENABLED = 'ENABLED';
    case DISABLED = 'DISABLED';
}
