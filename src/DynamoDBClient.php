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

            if (null !== $payload) {
                try {
                    $body = $this->serializer->serialize($payload, JsonEncoder::FORMAT);
                } catch (Throwable $exception) {
                    throw new RequestSerializationException($payload, $exception);
                }

                $request = $request->withBody($this->streamFactory->createStream($body));
            }

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
