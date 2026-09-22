<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient;

use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Message\BatchExecuteStatementRequest;
use Imper86\DynamoDBClient\Message\BatchExecuteStatementResponse;
use Imper86\DynamoDBClient\Message\BatchGetItemRequest;
use Imper86\DynamoDBClient\Message\BatchGetItemResponse;
use Imper86\DynamoDBClient\Message\BatchWriteItemRequest;
use Imper86\DynamoDBClient\Message\BatchWriteItemResponse;
use Imper86\DynamoDBClient\Message\CreateBackupRequest;
use Imper86\DynamoDBClient\Message\CreateBackupResponse;
use Imper86\DynamoDBClient\Message\CreateTableRequest;
use Imper86\DynamoDBClient\Message\CreateTableResponse;
use Imper86\DynamoDBClient\Message\DeleteBackupRequest;
use Imper86\DynamoDBClient\Message\DeleteBackupResponse;
use Imper86\DynamoDBClient\Message\DeleteItemRequest;
use Imper86\DynamoDBClient\Message\DeleteItemResponse;
use Imper86\DynamoDBClient\Message\DeleteResourcePolicyRequest;
use Imper86\DynamoDBClient\Message\DeleteResourcePolicyResponse;
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
    public function batchWriteItem(BatchWriteItemRequest $request): BatchWriteItemResponse;

    /**
     * @throws ExceptionInterface
     */
    public function createBackup(CreateBackupRequest $request): CreateBackupResponse;

    /**
     * @throws ExceptionInterface
     */
    public function createTable(CreateTableRequest $request): CreateTableResponse;

    /**
     * @throws ExceptionInterface
     */
    public function deleteBackup(DeleteBackupRequest $request): DeleteBackupResponse;

    /**
     * @throws ExceptionInterface
     */
    public function deleteItem(DeleteItemRequest $request): DeleteItemResponse;

    /**
     * @throws ExceptionInterface
     */
    public function deleteResourcePolicy(DeleteResourcePolicyRequest $request): DeleteResourcePolicyResponse;

    /**
     * @throws ExceptionInterface
     */
    public function getItem(GetItemRequest $request): GetItemResponse;
}
