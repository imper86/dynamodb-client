<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum StreamViewType: string
{
    case KEYS_ONLY = 'KEYS_ONLY';
    case NEW_AND_OLD_IMAGES = 'NEW_AND_OLD_IMAGES';
    case NEW_IMAGE = 'NEW_IMAGE';
    case OLD_IMAGE = 'OLD_IMAGE';
}
