<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient;

use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr17FactoryDiscovery;
use Imper86\DynamoDBClient\Exception\BadResponseException;
use LogicException;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\HttpClientException;
use Imper86\DynamoDBClient\Exception\InvalidArgumentException;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\RequestSerializationException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
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
use Imper86\DynamoDBClient\Message\ScanRequest;
use Imper86\DynamoDBClient\Message\ScanResponse;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\PluginClient\PluginClientFactory;
use Imper86\DynamoDBClient\Serializer\SerializerFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

use function get_debug_type;
use function sprintf;

final readonly class DynamoDBClient implements DynamoDBClientInterface
{
    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private SerializerInterface $serializer;

    /**
     * @param non-empty-string $region
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException when no credentials are given and the environment does not provide any
     * @throws NotFoundException
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $region,
        ?Credentials $credentials = null,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?SerializerInterface $serializer = null,
    ) {
        $this->httpClient = PluginClientFactory::create($region, $credentials, $httpClient);
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->serializer = $serializer ?? SerializerFactory::create();
    }

    public function batchExecuteStatement(BatchExecuteStatementRequest $request): BatchExecuteStatementResponse
    {
        return $this->sendRequest(
            'DynamoDB_20120810.BatchExecuteStatement',
            $request,
            BatchExecuteStatementResponse::class,
        );
    }

    public function batchGetItem(BatchGetItemRequest $request): BatchGetItemResponse
    {
        return $this->sendRequest('DynamoDB_20120810.BatchGetItem', $request, BatchGetItemResponse::class);
    }

    public function batchWriteItem(BatchWriteItemRequest $request): BatchWriteItemResponse
    {
        return $this->sendRequest('DynamoDB_20120810.BatchWriteItem', $request, BatchWriteItemResponse::class);
    }

    public function createBackup(CreateBackupRequest $request): CreateBackupResponse
    {
        return $this->sendRequest('DynamoDB_20120810.CreateBackup', $request, CreateBackupResponse::class);
    }

    public function createTable(CreateTableRequest $request): CreateTableResponse
    {
        return $this->sendRequest('DynamoDB_20120810.CreateTable', $request, CreateTableResponse::class);
    }

    public function deleteBackup(DeleteBackupRequest $request): DeleteBackupResponse
    {
        return $this->sendRequest('DynamoDB_20120810.DeleteBackup', $request, DeleteBackupResponse::class);
    }

    public function deleteItem(DeleteItemRequest $request): DeleteItemResponse
    {
        return $this->sendRequest('DynamoDB_20120810.DeleteItem', $request, DeleteItemResponse::class);
    }

    public function deleteResourcePolicy(DeleteResourcePolicyRequest $request): DeleteResourcePolicyResponse
    {
        return $this->sendRequest(
            'DynamoDB_20120810.DeleteResourcePolicy',
            $request,
            DeleteResourcePolicyResponse::class,
        );
    }

    public function deleteTable(DeleteTableRequest $request): DeleteTableResponse
    {
        return $this->sendRequest('DynamoDB_20120810.DeleteTable', $request, DeleteTableResponse::class);
    }

    public function describeBackup(DescribeBackupRequest $request): DescribeBackupResponse
    {
        return $this->sendRequest('DynamoDB_20120810.DescribeBackup', $request, DescribeBackupResponse::class);
    }

    public function describeContinuousBackups(
        DescribeContinuousBackupsRequest $request,
    ): DescribeContinuousBackupsResponse {
        return $this->sendRequest(
            'DynamoDB_20120810.DescribeContinuousBackups',
            $request,
            DescribeContinuousBackupsResponse::class,
        );
    }

    public function describeContributorInsights(
        DescribeContributorInsightsRequest $request,
    ): DescribeContributorInsightsResponse {
        return $this->sendRequest(
            'DynamoDB_20120810.DescribeContributorInsights',
            $request,
            DescribeContributorInsightsResponse::class,
        );
    }

    public function describeEndpoints(): DescribeEndpointsResponse
    {
        return $this->sendRequest('DynamoDB_20120810.DescribeEndpoints', null, DescribeEndpointsResponse::class);
    }

    public function describeExport(DescribeExportRequest $request): DescribeExportResponse
    {
        return $this->sendRequest('DynamoDB_20120810.DescribeExport', $request, DescribeExportResponse::class);
    }

    public function describeImport(DescribeImportRequest $request): DescribeImportResponse
    {
        return $this->sendRequest('DynamoDB_20120810.DescribeImport', $request, DescribeImportResponse::class);
    }

    public function describeKinesisStreamingDestination(
        DescribeKinesisStreamingDestinationRequest $request,
    ): DescribeKinesisStreamingDestinationResponse {
        return $this->sendRequest(
            'DynamoDB_20120810.DescribeKinesisStreamingDestination',
            $request,
            DescribeKinesisStreamingDestinationResponse::class,
        );
    }

    public function describeLimits(): DescribeLimitsResponse
    {
        return $this->sendRequest('DynamoDB_20120810.DescribeLimits', null, DescribeLimitsResponse::class);
    }

    public function describeTable(DescribeTableRequest $request): DescribeTableResponse
    {
        return $this->sendRequest('DynamoDB_20120810.DescribeTable', $request, DescribeTableResponse::class);
    }

    public function describeTableReplicaAutoScaling(
        DescribeTableReplicaAutoScalingRequest $request,
    ): DescribeTableReplicaAutoScalingResponse {
        return $this->sendRequest(
            'DynamoDB_20120810.DescribeTableReplicaAutoScaling',
            $request,
            DescribeTableReplicaAutoScalingResponse::class,
        );
    }

    public function describeTimeToLive(DescribeTimeToLiveRequest $request): DescribeTimeToLiveResponse
    {
        return $this->sendRequest('DynamoDB_20120810.DescribeTimeToLive', $request, DescribeTimeToLiveResponse::class);
    }

    public function disableKinesisStreamingDestination(
        DisableKinesisStreamingDestinationRequest $request,
    ): DisableKinesisStreamingDestinationResponse {
        return $this->sendRequest(
            'DynamoDB_20120810.DisableKinesisStreamingDestination',
            $request,
            DisableKinesisStreamingDestinationResponse::class,
        );
    }

    public function enableKinesisStreamingDestination(
        EnableKinesisStreamingDestinationRequest $request,
    ): EnableKinesisStreamingDestinationResponse {
        return $this->sendRequest(
            'DynamoDB_20120810.EnableKinesisStreamingDestination',
            $request,
            EnableKinesisStreamingDestinationResponse::class,
        );
    }

    public function executeStatement(ExecuteStatementRequest $request): ExecuteStatementResponse
    {
        return $this->sendRequest('DynamoDB_20120810.ExecuteStatement', $request, ExecuteStatementResponse::class);
    }

    public function executeTransaction(ExecuteTransactionRequest $request): ExecuteTransactionResponse
    {
        return $this->sendRequest(
            'DynamoDB_20120810.ExecuteTransaction',
            $request,
            ExecuteTransactionResponse::class,
        );
    }

    public function exportTableToPointInTime(
        ExportTableToPointInTimeRequest $request,
    ): ExportTableToPointInTimeResponse {
        return $this->sendRequest(
            'DynamoDB_20120810.ExportTableToPointInTime',
            $request,
            ExportTableToPointInTimeResponse::class,
        );
    }

    public function getItem(GetItemRequest $request): GetItemResponse
    {
        return $this->sendRequest('DynamoDB_20120810.GetItem', $request, GetItemResponse::class);
    }

    public function getResourcePolicy(GetResourcePolicyRequest $request): GetResourcePolicyResponse
    {
        return $this->sendRequest('DynamoDB_20120810.GetResourcePolicy', $request, GetResourcePolicyResponse::class);
    }

    public function importTable(ImportTableRequest $request): ImportTableResponse
    {
        return $this->sendRequest('DynamoDB_20120810.ImportTable', $request, ImportTableResponse::class);
    }

    public function listBackups(ListBackupsRequest $request = new ListBackupsRequest()): ListBackupsResponse
    {
        return $this->sendRequest('DynamoDB_20120810.ListBackups', $request, ListBackupsResponse::class);
    }

    public function listContributorInsights(
        ListContributorInsightsRequest $request = new ListContributorInsightsRequest(),
    ): ListContributorInsightsResponse {
        return $this->sendRequest(
            'DynamoDB_20120810.ListContributorInsights',
            $request,
            ListContributorInsightsResponse::class,
        );
    }

    public function listExports(ListExportsRequest $request = new ListExportsRequest()): ListExportsResponse
    {
        return $this->sendRequest('DynamoDB_20120810.ListExports', $request, ListExportsResponse::class);
    }

    public function listImports(ListImportsRequest $request = new ListImportsRequest()): ListImportsResponse
    {
        return $this->sendRequest('DynamoDB_20120810.ListImports', $request, ListImportsResponse::class);
    }

    public function listTables(ListTablesRequest $request = new ListTablesRequest()): ListTablesResponse
    {
        return $this->sendRequest('DynamoDB_20120810.ListTables', $request, ListTablesResponse::class);
    }

    public function listTagsOfResource(ListTagsOfResourceRequest $request): ListTagsOfResourceResponse
    {
        return $this->sendRequest('DynamoDB_20120810.ListTagsOfResource', $request, ListTagsOfResourceResponse::class);
    }

    public function putItem(PutItemRequest $request): PutItemResponse
    {
        return $this->sendRequest('DynamoDB_20120810.PutItem', $request, PutItemResponse::class);
    }

    public function putResourcePolicy(PutResourcePolicyRequest $request): PutResourcePolicyResponse
    {
        return $this->sendRequest('DynamoDB_20120810.PutResourcePolicy', $request, PutResourcePolicyResponse::class);
    }

    public function query(QueryRequest $request): QueryResponse
    {
        return $this->sendRequest('DynamoDB_20120810.Query', $request, QueryResponse::class);
    }

    public function restoreTableFromBackup(RestoreTableFromBackupRequest $request): RestoreTableFromBackupResponse
    {
        return $this->sendRequest('DynamoDB_20120810.RestoreTableFromBackup', $request, RestoreTableFromBackupResponse::class);
    }

    public function restoreTableToPointInTime(RestoreTableToPointInTimeRequest $request): RestoreTableToPointInTimeResponse
    {
        return $this->sendRequest('DynamoDB_20120810.RestoreTableToPointInTime', $request, RestoreTableToPointInTimeResponse::class);
    }

    public function scan(ScanRequest $request): ScanResponse
    {
        return $this->sendRequest('DynamoDB_20120810.Scan', $request, ScanResponse::class);
    }

    /**
     * @template T of object
     * @param null|class-string<T> $expectedResponseType
     * @return ($expectedResponseType is null ? null : T)
     * @throws ExceptionInterface
     */
    private function sendRequest(
        string $target,
        ?object $payload = null,
        ?string $expectedResponseType = null,
    ): ?object {
        try {
            $request = $this->requestFactory
                ->createRequest('POST', '/')
                ->withHeader('X-Amz-Target', $target)
            ;

            // An operation without parameters still sends a JSON object, just an empty one.
            $body = '{}';

            if (null !== $payload) {
                try {
                    $body = $this->serializer->serialize($payload, JsonEncoder::FORMAT);
                } catch (Throwable $exception) {
                    throw new RequestSerializationException($payload, $exception);
                }
            }

            $request = $request->withBody($this->streamFactory->createStream($body));

            try {
                $response = $this->httpClient->sendRequest($request);
            } catch (ClientExceptionInterface $exception) {
                throw HttpClientException::from($exception);
            }

            if (200 !== $response->getStatusCode()) {
                throw new BadResponseException($response);
            }

            if (null === $expectedResponseType) {
                return null;
            }

            try {
                $deserialized = $this->serializer->deserialize(
                    $response->getBody()->__toString(),
                    $expectedResponseType,
                    JsonEncoder::FORMAT,
                );

                if (!$deserialized instanceof $expectedResponseType) {
                    throw new LogicException(
                        sprintf(
                            'Unexpected result from deserializer. Expected %s, got %s',
                            $expectedResponseType,
                            get_debug_type($deserialized),
                        ),
                    );
                }

                return $deserialized;
            } catch (Throwable $exception) {
                throw new ResponseDeserializationException($response, $exception);
            }
        } catch (\InvalidArgumentException $exception) {
            throw InvalidArgumentException::from($exception);
        }
    }
}
