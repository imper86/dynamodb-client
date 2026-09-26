<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\TimeToLiveDescription;

final class DescribeTimeToLiveResponse
{
    public function __construct(
        public readonly ?TimeToLiveDescription $timeToLiveDescription = null,
    ) {}
}
