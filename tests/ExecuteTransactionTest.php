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
use Imper86\DynamoDBClient\Message\ExecuteTransactionRequest;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueList;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ConsumedCapacityList;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\ItemResponse;
use Imper86\DynamoDBClient\Model\ParameterizedStatement;
use Imper86\DynamoDBClient\Model\ParameterizedStatementList;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\ReturnValuesOnConditionCheckFailure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The ExecuteTransaction reference has no Examples section, so the fixtures are built from its request
 * and response syntax: a read transaction of two songs in a `Music` table, one of which does not exist.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_ExecuteTransaction.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class ExecuteTransactionTest extends TestCase
{
    private const string STATEMENT = 'SELECT * FROM "Music" WHERE "Artist" = ? AND "SongTitle" = ?';

    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/execute-transaction-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/execute-transaction-response.json';

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

        $this->createClient($httpClient)->executeTransaction($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.ExecuteTransaction', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheResponsesInTheOrderOfTheStatements(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->executeTransaction($this->documentedRequest());

        self::assertCount(2, $response->responses);

        $found = $response->responses->get(0);

        self::assertInstanceOf(ItemResponse::class, $found);

        $item = $found->item;

        self::assertInstanceOf(AttributeValueMap::class, $item);
        self::assertSame('Happy Day', $item->get('SongTitle')?->string);
        self::assertSame('Songs About Life', $item->get('AlbumTitle')?->string);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheItemNullForAStatementThatMatchedNothing(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->executeTransaction($this->documentedRequest());

        $missing = $response->responses->get(1);

        self::assertInstanceOf(ItemResponse::class, $missing);
        self::assertNull($missing->item);
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

        $response = $this->createClient($httpClient)->executeTransaction($this->documentedRequest());

        $consumedCapacity = $response->consumedCapacity;

        self::assertInstanceOf(ConsumedCapacityList::class, $consumedCapacity);
        self::assertCount(2, $consumedCapacity);
        self::assertSame(2.0, $consumedCapacity->get(1)?->capacityUnits);
        self::assertSame('Music', $consumedCapacity->get(1)?->tableName);
    }

    /**
     * A write transaction answers with no responses at all.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheElementsTheServiceOmitsEmpty(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->executeTransaction($this->documentedRequest());

        self::assertTrue($response->responses->isEmpty());
        self::assertNull($response->consumedCapacity);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testFailsOnABodyItCannotDeserialize(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Responses":{"Item":{}}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->executeTransaction($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyTransaction(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExecuteTransactionRequest(new ParameterizedStatementList());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATransactionLongerThanTheServiceAccepts(): void
    {
        $statements = [];

        for ($i = 0; $i < 101; ++$i) {
            $statements[] = new ParameterizedStatement(self::STATEMENT);
        }

        $this->expectException(InvalidArgumentException::class);

        new ExecuteTransactionRequest(new ParameterizedStatementList($statements));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAClientRequestTokenLongerThanTheServiceAccepts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExecuteTransactionRequest(
            new ParameterizedStatementList([new ParameterizedStatement(self::STATEMENT)]),
            clientRequestToken: str_repeat('a', 37),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAStatementLongerThanTheServiceAccepts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ParameterizedStatement(str_repeat('a', 8193));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyParameterList(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ParameterizedStatement(self::STATEMENT, new AttributeValueList());
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): ExecuteTransactionRequest
    {
        return new ExecuteTransactionRequest(
            transactStatements: new ParameterizedStatementList([
                new ParameterizedStatement(
                    statement: self::STATEMENT,
                    parameters: new AttributeValueList([
                        AttributeValue::string('Acme Band'),
                        AttributeValue::string('Happy Day'),
                    ]),
                ),
                new ParameterizedStatement(
                    statement: self::STATEMENT,
                    parameters: new AttributeValueList([
                        AttributeValue::string('Acme Band'),
                        AttributeValue::string('PartiQL Rocks'),
                    ]),
                    returnValuesOnConditionCheckFailure: ReturnValuesOnConditionCheckFailure::ALL_OLD,
                ),
            ]),
            clientRequestToken: '5f2a4b1c-8d3e-4f60-9a7b-0c1d2e3f4a5b',
            returnConsumedCapacity: ReturnConsumedCapacity::TOTAL,
        );
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

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
