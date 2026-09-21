<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient;

use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Message\GetItemRequest;
use Imper86\DynamoDBClient\Message\GetItemResponse;

interface DynamoDBClientInterface
{
    /**
     * @throws ExceptionInterface
     */
    public function getItem(GetItemRequest $request): GetItemResponse;
}
