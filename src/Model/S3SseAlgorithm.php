<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum S3SseAlgorithm: string
{
    case AES256 = 'AES256';
    case KMS = 'KMS';
}
