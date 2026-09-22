<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum InputCompressionType: string
{
    case GZIP = 'GZIP';
    case NONE = 'NONE';
    case ZSTD = 'ZSTD';
}
