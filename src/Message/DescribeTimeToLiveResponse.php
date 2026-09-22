<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\TimeToLiveDescription;

final readonly class DescribeTimeToLiveResponse
{
    public function __construct(
        public ?TimeToLiveDescription $timeToLiveDescription = null,
    ) {}
}
