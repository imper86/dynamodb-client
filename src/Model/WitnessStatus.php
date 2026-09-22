<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum WitnessStatus: string
{
    case ACTIVE = 'ACTIVE';
    case CREATING = 'CREATING';
    case DELETING = 'DELETING';
}
