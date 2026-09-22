<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum BillingMode: string
{
    case PAY_PER_REQUEST = 'PAY_PER_REQUEST';
    case PROVISIONED = 'PROVISIONED';
}
