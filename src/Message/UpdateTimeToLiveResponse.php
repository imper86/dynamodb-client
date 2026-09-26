<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\TimeToLiveSpecification;

final class UpdateTimeToLiveResponse
{
    public function __construct(
        public readonly ?TimeToLiveSpecification $timeToLiveSpecification = null,
    ) {}
}
