<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum VectorDistanceFunction: string
{
    case COSINE = 'COSINE';
    case DOT_PRODUCT = 'DOT_PRODUCT';
    case EUCLIDEAN = 'EUCLIDEAN';
}
