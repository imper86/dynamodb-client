<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum ContributorInsightsAction: string
{
    case ENABLE = 'ENABLE';
    case DISABLE = 'DISABLE';
}
