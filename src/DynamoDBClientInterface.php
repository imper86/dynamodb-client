<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient;

use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Message\BatchExecuteStatementRequest;
use Imper86\DynamoDBClient\Message\BatchExecuteStatementResponse;
use Imper86\DynamoDBClient\Message\BatchGetItemRequest;
use Imper86\DynamoDBClient\Message\BatchGetItemResponse;
use Imper86\DynamoDBClient\Message\GetItemRequest;
use Imper86\DynamoDBClient\Message\GetItemResponse;

interface DynamoDBClientInterface
{
    /**
     * @throws ExceptionInterface
     */
    public function batchExecuteStatement(BatchExecuteStatementRequest $request): BatchExecuteStatementResponse;

    /**
     * @throws ExceptionInterface
     */
    public function batchGetItem(BatchGetItemRequest $request): BatchGetItemResponse;

    /**
     * @throws ExceptionInterface
     */
    public function getItem(GetItemRequest $request): GetItemResponse;
}
