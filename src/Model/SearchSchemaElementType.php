<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum SearchSchemaElementType: string
{
    case HASH = 'HASH';
    case INLINE_FILTER = 'INLINE_FILTER';
}
