<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum ImportStatus: string
{
    case CANCELLED = 'CANCELLED';
    case CANCELLING = 'CANCELLING';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';
    case IN_PROGRESS = 'IN_PROGRESS';
}
