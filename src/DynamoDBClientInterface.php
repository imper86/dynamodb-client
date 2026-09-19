<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient;

use OoAws\DynamoDBClient\Request\GetItemRequest;

interface DynamoDBClientInterface
{
    public function getItem(GetItemRequest $request): void;
}
