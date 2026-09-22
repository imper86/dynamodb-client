<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum TimeToLiveStatus: string
{
    case DISABLED = 'DISABLED';
    case DISABLING = 'DISABLING';
    case ENABLED = 'ENABLED';
    case ENABLING = 'ENABLING';
}
