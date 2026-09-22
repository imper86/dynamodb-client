<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum ContributorInsightsStatus: string
{
    case ENABLING = 'ENABLING';
    case ENABLED = 'ENABLED';
    case DISABLING = 'DISABLING';
    case DISABLED = 'DISABLED';
    case FAILED = 'FAILED';
}
