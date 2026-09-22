<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum ContributorInsightsMode: string
{
    case ACCESSED_AND_THROTTLED_KEYS = 'ACCESSED_AND_THROTTLED_KEYS';
    case THROTTLED_KEYS = 'THROTTLED_KEYS';
}
