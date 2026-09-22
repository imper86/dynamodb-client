<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum TableStatus: string
{
    case ACTIVE = 'ACTIVE';
    case ARCHIVED = 'ARCHIVED';
    case ARCHIVING = 'ARCHIVING';
    case CREATING = 'CREATING';
    case DELETING = 'DELETING';
    case INACCESSIBLE_ENCRYPTION_CREDENTIALS = 'INACCESSIBLE_ENCRYPTION_CREDENTIALS';
    case REPLICATION_NOT_AUTHORIZED = 'REPLICATION_NOT_AUTHORIZED';
    case UPDATING = 'UPDATING';
}
