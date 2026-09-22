<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum MultiRegionConsistency: string
{
    case EVENTUAL = 'EVENTUAL';
    case STRONG = 'STRONG';
}
