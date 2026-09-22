<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum ApproximateCreationDateTimePrecision: string
{
    case MICROSECOND = 'MICROSECOND';
    case MILLISECOND = 'MILLISECOND';
}
