<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum IndexStatus: string
{
    case ACTIVE = 'ACTIVE';
    case CREATING = 'CREATING';
    case DELETING = 'DELETING';
    case UPDATING = 'UPDATING';
}
