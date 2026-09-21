<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum ReturnItemCollectionMetrics: string
{
    case SIZE = 'SIZE';
    case NONE = 'NONE';
}
