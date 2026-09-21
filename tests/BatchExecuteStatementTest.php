<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\BatchExecuteStatementRequest;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueList;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\BatchStatementError;
use Imper86\DynamoDBClient\Model\BatchStatementErrorCode;
use Imper86\DynamoDBClient\Model\BatchStatementRequest;
use Imper86\DynamoDBClient\Model\BatchStatementRequestList;
use Imper86\DynamoDBClient\Model\BatchStatementResponse;
use Imper86\DynamoDBClient\Model\ConsumedCapacityList;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\ReturnValuesOnConditionCheckFailure;
use Imper86\DynamoDBClient\ValueObject\StringSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_BatchExecuteStatement.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class BatchExecuteStatementTest extends TestCase
{
    private const string STATEMENT = 'SELECT * FROM "Thread" WHERE "ForumName" = ? AND "Subject" = ?';

    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/batch-execute-statement-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/batch-execute-statement-response.json';

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSendsTheRequestTheWayTheApiReferenceDocumentsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $this->createClient($httpClient)->batchExecuteStatement($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('POST', $sent->getMethod());
        self::assertSame('https://dynamodb.eu-central-1.amazonaws.com/', $sent->getUri()->__toString());
        self::assertSame('DynamoDB_20120810.BatchExecuteStatement', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheStatementResponsesInTheOrderOfTheStatements(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->batchExecuteStatement($this->documentedRequest());

        self::assertCount(2, $response->responses);

        $succeeded = $response->responses->get(0);

        self::assertInstanceOf(BatchStatementResponse::class, $succeeded);
        self::assertSame('Thread', $succeeded->tableName);
        self::assertNull($succeeded->error);

        $item = $succeeded->item;

        self::assertInstanceOf(AttributeValueMap::class, $item);
        self::assertSame('201303190436', $item->get('LastPostDateTime')?->string);

        $tags = $item->get('Tags')?->stringSet;

        self::assertInstanceOf(StringSet::class, $tags);
        self::assertSame(['Update', 'Multiple Items', 'HelpMe'], $tags->toArray());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReportsTheErrorOfAFailedStatementWithinASuccessfulResponse(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->batchExecuteStatement($this->documentedRequest());

        $failed = $response->responses->get(1);

        self::assertInstanceOf(BatchStatementResponse::class, $failed);
        self::assertNull($failed->item);

        $error = $failed->error;

        self::assertInstanceOf(BatchStatementError::class, $error);
        self::assertSame(BatchStatementErrorCode::CONDITIONAL_CHECK_FAILED, $error->code);
        self::assertSame('The conditional request failed', $error->message);
        self::assertSame('201303190505', $error->item?->get('LastPostDateTime')?->string);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheConsumedCapacityOfEveryStatement(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->batchExecuteStatement($this->documentedRequest());

        $consumedCapacity = $response->consumedCapacity;

        self::assertInstanceOf(ConsumedCapacityList::class, $consumedCapacity);
        self::assertCount(2, $consumedCapacity);
        self::assertSame(1.0, $consumedCapacity->get(0)?->capacityUnits);
        self::assertSame(0.5, $consumedCapacity->get(1)?->capacityUnits);
        self::assertSame('Thread', $consumedCapacity->get(0)?->tableName);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheConsumedCapacityEmptyWhenTheServiceOmitsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Responses":[{"TableName":"Thread"}]}'));

        $response = $this->createClient($httpClient)->batchExecuteStatement($this->documentedRequest());

        self::assertNull($response->consumedCapacity);
        self::assertCount(1, $response->responses);
    }

    /**
     * The service always answers a successful batch with one response per statement, but it does not
     * promise the element, and an absent one is not worth failing the whole call over.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheResponsesEmptyWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"ConsumedCapacity":[{"CapacityUnits":1,"TableName":"Thread"}]}'));

        $response = $this->createClient($httpClient)->batchExecuteStatement($this->documentedRequest());

        self::assertTrue($response->responses->isEmpty());
        self::assertCount(1, $response->consumedCapacity ?? []);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testWrapsAResponseItCannotDeserialize(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Responses":{"TableName":"Thread"}}'));

        try {
            $this->createClient($httpClient)->batchExecuteStatement($this->documentedRequest());
            self::fail('Expected a ' . ResponseDeserializationException::class . '.');
        } catch (ResponseDeserializationException $exception) {
            self::assertSame(200, $exception->response->getStatusCode());
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyBatch(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new BatchExecuteStatementRequest(new BatchStatementRequestList());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsABatchLongerThanTheServiceAccepts(): void
    {
        $statements = [];

        for ($i = 0; $i < 26; ++$i) {
            $statements[] = new BatchStatementRequest(self::STATEMENT);
        }

        $this->expectException(InvalidArgumentException::class);

        new BatchExecuteStatementRequest(new BatchStatementRequestList($statements));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAStatementLongerThanTheServiceAccepts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new BatchStatementRequest(str_repeat('a', 8193));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyParameterList(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new BatchStatementRequest(self::STATEMENT, parameters: new AttributeValueList());
    }

    /**
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    private function createClient(MockClient $httpClient): DynamoDBClient
    {
        return new DynamoDBClient(
            'eu-central-1',
            new Credentials('AKIDEXAMPLE', 'secret'),
            $httpClient,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): BatchExecuteStatementRequest
    {
        return new BatchExecuteStatementRequest(
            statements: new BatchStatementRequestList([
                new BatchStatementRequest(
                    statement: self::STATEMENT,
                    consistentRead: true,
                    parameters: new AttributeValueList([
                        new AttributeValue(string: 'Amazon DynamoDB'),
                        new AttributeValue(string: 'How do I update multiple items?'),
                    ]),
                ),
                new BatchStatementRequest(
                    statement: self::STATEMENT,
                    consistentRead: true,
                    parameters: new AttributeValueList([
                        new AttributeValue(string: 'Amazon DynamoDB'),
                        new AttributeValue(string: 'How do I delete an item?'),
                    ]),
                    returnValuesOnConditionCheckFailure: ReturnValuesOnConditionCheckFailure::ALL_OLD,
                ),
            ]),
            returnConsumedCapacity: ReturnConsumedCapacity::TOTAL,
        );
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
