<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final readonly class BillingModeSummary
{
    public function __construct(
        public ?BillingMode $billingMode = null,
        public ?DateTimeImmutable $lastUpdateToPayPerRequestDateTime = null,
    ) {}
}
