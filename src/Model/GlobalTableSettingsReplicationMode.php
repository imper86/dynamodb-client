<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum GlobalTableSettingsReplicationMode: string
{
    case DISABLED = 'DISABLED';
    case ENABLED = 'ENABLED';
    case ENABLED_WITH_OVERRIDES = 'ENABLED_WITH_OVERRIDES';
}
