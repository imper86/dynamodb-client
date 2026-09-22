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
use Imper86\DynamoDBClient\Message\GetItemRequest;
use Imper86\DynamoDBClient\Message\GetItemResponse;
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

    public function getItem(GetItemRequest $request): GetItemResponse
    {
        return $this->sendRequest('DynamoDB_20120810.GetItem', $request, GetItemResponse::class);
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
