<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum DestinationStatus: string
{
    case ACTIVE = 'ACTIVE';
    case DISABLED = 'DISABLED';
    case DISABLING = 'DISABLING';
    case ENABLE_FAILED = 'ENABLE_FAILED';
    case ENABLING = 'ENABLING';
    case UPDATING = 'UPDATING';
}
