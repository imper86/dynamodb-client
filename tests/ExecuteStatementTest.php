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
use Imper86\DynamoDBClient\Message\ExecuteStatementRequest;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueList;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ConsumedCapacity;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\ReturnValuesOnConditionCheckFailure;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function json_decode;
use function str_repeat;

use const JSON_THROW_ON_ERROR;

/**
 * The ExecuteStatement reference has no Examples section, so the fixtures are built from its request
 * and response syntax: a paged `SELECT` of the songs of one artist in a `Music` table.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_ExecuteStatement.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class ExecuteStatementTest extends TestCase
{
    private const string STATEMENT = 'SELECT * FROM "Music" WHERE "Artist" = ?';

    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/execute-statement-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/execute-statement-response.json';

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

        $this->createClient($httpClient)->executeStatement($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.ExecuteStatement', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     * @throws JsonException
     */
    public function testSendsTheNextTokenOfAFollowUpRequest(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Items":[]}'));

        $this->createClient($httpClient)->executeStatement(
            new ExecuteStatementRequest(self::STATEMENT, nextToken: 'eyJ2IjoxfQ=='),
        );

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame(
            ['Statement' => self::STATEMENT, 'NextToken' => 'eyJ2IjoxfQ=='],
            json_decode($sent->getBody()->__toString(), true, flags: JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheItemsTheStatementRead(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->executeStatement($this->documentedRequest());

        self::assertCount(2, $response->items);

        $first = $response->items->get(0);

        self::assertInstanceOf(AttributeValueMap::class, $first);
        self::assertSame('Happy Day', $first->get('SongTitle')?->string);
        self::assertSame('Songs About Life', $first->get('AlbumTitle')?->string);

        $second = $response->items->get(1);

        self::assertInstanceOf(AttributeValueMap::class, $second);
        self::assertSame('10', $second->get('Awards')?->number);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsWhereToContinueAPartialRead(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->executeStatement($this->documentedRequest());

        self::assertSame('eyJ2IjoxLCJ0b2tlbiI6Ik11c2ljIn0=', $response->nextToken);

        $lastEvaluatedKey = $response->lastEvaluatedKey;

        self::assertInstanceOf(AttributeValueMap::class, $lastEvaluatedKey);
        self::assertSame(['Artist', 'SongTitle'], $lastEvaluatedKey->keys());
        self::assertSame('PartiQL Rocks', $lastEvaluatedKey->get('SongTitle')?->string);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheConsumedCapacity(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->executeStatement($this->documentedRequest());

        $consumedCapacity = $response->consumedCapacity;

        self::assertInstanceOf(ConsumedCapacity::class, $consumedCapacity);
        self::assertSame(1.0, $consumedCapacity->capacityUnits);
        self::assertSame('Music', $consumedCapacity->tableName);
    }

    /**
     * A write, or the last page of a read, comes back without a key or token to continue from.
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

        $response = $this->createClient($httpClient)->executeStatement($this->documentedRequest());

        self::assertTrue($response->items->isEmpty());
        self::assertNull($response->lastEvaluatedKey);
        self::assertNull($response->nextToken);
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
        $httpClient->addResponse(new Response(body: '{"Items":{"Artist":{"S":"Acme Band"}}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->executeStatement($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAStatementLongerThanTheServiceAccepts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExecuteStatementRequest(str_repeat('a', 8193));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsANextTokenLongerThanTheServiceAccepts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExecuteStatementRequest(self::STATEMENT, nextToken: str_repeat('a', 32769));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyParameterList(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExecuteStatementRequest(self::STATEMENT, parameters: new AttributeValueList());
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): ExecuteStatementRequest
    {
        return new ExecuteStatementRequest(
            statement: self::STATEMENT,
            consistentRead: true,
            limit: 2,
            parameters: new AttributeValueList([AttributeValue::string('Acme Band')]),
            returnConsumedCapacity: ReturnConsumedCapacity::TOTAL,
            returnValuesOnConditionCheckFailure: ReturnValuesOnConditionCheckFailure::NONE,
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
