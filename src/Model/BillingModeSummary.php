<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final class BillingModeSummary
{
    public function __construct(
        public readonly ?BillingMode $billingMode = null,
        public readonly ?DateTimeImmutable $lastUpdateToPayPerRequestDateTime = null,
    ) {}
}
