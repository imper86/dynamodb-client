<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\EndpointList;

final class DescribeEndpointsResponse
{
    public function __construct(
        public readonly EndpointList $endpoints = new EndpointList(),
    ) {}
}
