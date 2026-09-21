<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient;

use OoAws\DynamoDBClient\Exception\ExceptionInterface;
use OoAws\DynamoDBClient\Message\GetItemRequest;
use OoAws\DynamoDBClient\Message\GetItemResponse;

interface DynamoDBClientInterface
{
    /**
     * @throws ExceptionInterface
     */
    public function getItem(GetItemRequest $request): GetItemResponse;
}
