<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum AttributeAction: string
{
    case ADD = 'ADD';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
}
