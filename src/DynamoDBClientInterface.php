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
use Imper86\DynamoDBClient\Message\DeleteTableRequest;
use Imper86\DynamoDBClient\Message\DeleteTableResponse;
use Imper86\DynamoDBClient\Message\DescribeBackupRequest;
use Imper86\DynamoDBClient\Message\DescribeBackupResponse;
use Imper86\DynamoDBClient\Message\DescribeContinuousBackupsRequest;
use Imper86\DynamoDBClient\Message\DescribeContinuousBackupsResponse;
use Imper86\DynamoDBClient\Message\DescribeContributorInsightsRequest;
use Imper86\DynamoDBClient\Message\DescribeContributorInsightsResponse;
use Imper86\DynamoDBClient\Message\DescribeEndpointsResponse;
use Imper86\DynamoDBClient\Message\DescribeExportRequest;
use Imper86\DynamoDBClient\Message\DescribeExportResponse;
use Imper86\DynamoDBClient\Message\DescribeImportRequest;
use Imper86\DynamoDBClient\Message\DescribeImportResponse;
use Imper86\DynamoDBClient\Message\DescribeKinesisStreamingDestinationRequest;
use Imper86\DynamoDBClient\Message\DescribeKinesisStreamingDestinationResponse;
use Imper86\DynamoDBClient\Message\DescribeLimitsResponse;
use Imper86\DynamoDBClient\Message\DescribeTableRequest;
use Imper86\DynamoDBClient\Message\DescribeTableReplicaAutoScalingRequest;
use Imper86\DynamoDBClient\Message\DescribeTableReplicaAutoScalingResponse;
use Imper86\DynamoDBClient\Message\DescribeTableResponse;
use Imper86\DynamoDBClient\Message\DescribeTimeToLiveRequest;
use Imper86\DynamoDBClient\Message\DescribeTimeToLiveResponse;
use Imper86\DynamoDBClient\Message\DisableKinesisStreamingDestinationRequest;
use Imper86\DynamoDBClient\Message\DisableKinesisStreamingDestinationResponse;
use Imper86\DynamoDBClient\Message\EnableKinesisStreamingDestinationRequest;
use Imper86\DynamoDBClient\Message\EnableKinesisStreamingDestinationResponse;
use Imper86\DynamoDBClient\Message\ExecuteStatementRequest;
use Imper86\DynamoDBClient\Message\ExecuteStatementResponse;
use Imper86\DynamoDBClient\Message\ExecuteTransactionRequest;
use Imper86\DynamoDBClient\Message\ExecuteTransactionResponse;
use Imper86\DynamoDBClient\Message\ExportTableToPointInTimeRequest;
use Imper86\DynamoDBClient\Message\ExportTableToPointInTimeResponse;
use Imper86\DynamoDBClient\Message\GetItemRequest;
use Imper86\DynamoDBClient\Message\GetItemResponse;
use Imper86\DynamoDBClient\Message\GetResourcePolicyRequest;
use Imper86\DynamoDBClient\Message\GetResourcePolicyResponse;
use Imper86\DynamoDBClient\Message\ImportTableRequest;
use Imper86\DynamoDBClient\Message\ImportTableResponse;
use Imper86\DynamoDBClient\Message\ListBackupsRequest;
use Imper86\DynamoDBClient\Message\ListBackupsResponse;
use Imper86\DynamoDBClient\Message\ListContributorInsightsRequest;
use Imper86\DynamoDBClient\Message\ListContributorInsightsResponse;
use Imper86\DynamoDBClient\Message\ListExportsRequest;
use Imper86\DynamoDBClient\Message\ListExportsResponse;
use Imper86\DynamoDBClient\Message\ListImportsRequest;
use Imper86\DynamoDBClient\Message\ListImportsResponse;
use Imper86\DynamoDBClient\Message\ListTablesRequest;
use Imper86\DynamoDBClient\Message\ListTablesResponse;
use Imper86\DynamoDBClient\Message\ListTagsOfResourceRequest;
use Imper86\DynamoDBClient\Message\ListTagsOfResourceResponse;
use Imper86\DynamoDBClient\Message\PutItemRequest;
use Imper86\DynamoDBClient\Message\PutItemResponse;
use Imper86\DynamoDBClient\Message\PutResourcePolicyRequest;
use Imper86\DynamoDBClient\Message\PutResourcePolicyResponse;
use Imper86\DynamoDBClient\Message\QueryRequest;
use Imper86\DynamoDBClient\Message\QueryResponse;
use Imper86\DynamoDBClient\Message\RestoreTableFromBackupRequest;
use Imper86\DynamoDBClient\Message\RestoreTableFromBackupResponse;
use Imper86\DynamoDBClient\Message\RestoreTableToPointInTimeRequest;
use Imper86\DynamoDBClient\Message\RestoreTableToPointInTimeResponse;

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
    public function deleteTable(DeleteTableRequest $request): DeleteTableResponse;

    /**
     * @throws ExceptionInterface
     */
    public function describeBackup(DescribeBackupRequest $request): DescribeBackupResponse;

    /**
     * @throws ExceptionInterface
     */
    public function describeContinuousBackups(
        DescribeContinuousBackupsRequest $request,
    ): DescribeContinuousBackupsResponse;

    /**
     * @throws ExceptionInterface
     */
    public function describeContributorInsights(
        DescribeContributorInsightsRequest $request,
    ): DescribeContributorInsightsResponse;

    /**
     * @throws ExceptionInterface
     */
    public function describeEndpoints(): DescribeEndpointsResponse;

    /**
     * @throws ExceptionInterface
     */
    public function describeExport(DescribeExportRequest $request): DescribeExportResponse;

    /**
     * @throws ExceptionInterface
     */
    public function describeImport(DescribeImportRequest $request): DescribeImportResponse;

    /**
     * @throws ExceptionInterface
     */
    public function describeKinesisStreamingDestination(
        DescribeKinesisStreamingDestinationRequest $request,
    ): DescribeKinesisStreamingDestinationResponse;

    /**
     * @throws ExceptionInterface
     */
    public function describeLimits(): DescribeLimitsResponse;

    /**
     * @throws ExceptionInterface
     */
    public function describeTable(DescribeTableRequest $request): DescribeTableResponse;

    /**
     * @throws ExceptionInterface
     */
    public function describeTableReplicaAutoScaling(
        DescribeTableReplicaAutoScalingRequest $request,
    ): DescribeTableReplicaAutoScalingResponse;

    /**
     * @throws ExceptionInterface
     */
    public function describeTimeToLive(DescribeTimeToLiveRequest $request): DescribeTimeToLiveResponse;

    /**
     * @throws ExceptionInterface
     */
    public function disableKinesisStreamingDestination(
        DisableKinesisStreamingDestinationRequest $request,
    ): DisableKinesisStreamingDestinationResponse;

    /**
     * @throws ExceptionInterface
     */
    public function enableKinesisStreamingDestination(
        EnableKinesisStreamingDestinationRequest $request,
    ): EnableKinesisStreamingDestinationResponse;

    /**
     * @throws ExceptionInterface
     */
    public function executeStatement(ExecuteStatementRequest $request): ExecuteStatementResponse;

    /**
     * @throws ExceptionInterface
     */
    public function executeTransaction(ExecuteTransactionRequest $request): ExecuteTransactionResponse;

    /**
     * @throws ExceptionInterface
     */
    public function exportTableToPointInTime(
        ExportTableToPointInTimeRequest $request,
    ): ExportTableToPointInTimeResponse;

    /**
     * @throws ExceptionInterface
     */
    public function getItem(GetItemRequest $request): GetItemResponse;

    /**
     * @throws ExceptionInterface
     */
    public function getResourcePolicy(GetResourcePolicyRequest $request): GetResourcePolicyResponse;

    /**
     * @throws ExceptionInterface
     */
    public function importTable(ImportTableRequest $request): ImportTableResponse;

    /**
     * @throws ExceptionInterface
     */
    public function listBackups(ListBackupsRequest $request = new ListBackupsRequest()): ListBackupsResponse;

    /**
     * @throws ExceptionInterface
     */
    public function listContributorInsights(
        ListContributorInsightsRequest $request = new ListContributorInsightsRequest(),
    ): ListContributorInsightsResponse;

    /**
     * @throws ExceptionInterface
     */
    public function listExports(ListExportsRequest $request = new ListExportsRequest()): ListExportsResponse;

    /**
     * @throws ExceptionInterface
     */
    public function listImports(ListImportsRequest $request = new ListImportsRequest()): ListImportsResponse;

    /**
     * @throws ExceptionInterface
     */
    public function listTables(ListTablesRequest $request = new ListTablesRequest()): ListTablesResponse;

    /**
     * @throws ExceptionInterface
     */
    public function listTagsOfResource(ListTagsOfResourceRequest $request): ListTagsOfResourceResponse;

    /**
     * @throws ExceptionInterface
     */
    public function putItem(PutItemRequest $request): PutItemResponse;

    /**
     * @throws ExceptionInterface
     */
    public function putResourcePolicy(PutResourcePolicyRequest $request): PutResourcePolicyResponse;

    /**
     * @throws ExceptionInterface
     */
    public function query(QueryRequest $request): QueryResponse;

    /**
     * @throws ExceptionInterface
     */
    public function restoreTableFromBackup(RestoreTableFromBackupRequest $request): RestoreTableFromBackupResponse;

    /**
     * @throws ExceptionInterface
     */
    public function restoreTableToPointInTime(RestoreTableToPointInTimeRequest $request): RestoreTableToPointInTimeResponse;
}
