<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\EndpointList;

final readonly class DescribeEndpointsResponse
{
    public function __construct(
        public EndpointList $endpoints = new EndpointList(),
    ) {}
}
