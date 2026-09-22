<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\ListTablesRequest;
use Imper86\DynamoDBClient\Model\Credentials;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The fixtures are the "List Tables" example: three table names, starting with `Forum`.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_ListTables.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class ListTablesTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/list-tables-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/list-tables-response.json';

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

        $this->createClient($httpClient)->listTables(new ListTablesRequest(exclusiveStartTableName: 'Forum', limit: 3));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.ListTables', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSendsAnEmptyObjectWithoutARequest(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $this->createClient($httpClient)->listTables();

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('{}', $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheTableNamesAndWhereTheNextPageStarts(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->listTables();

        self::assertSame(['Forum', 'Reply', 'Thread'], $response->tableNames->toArray());
        self::assertSame('Thread', $response->lastEvaluatedTableName);
    }

    /**
     * The last page has no `LastEvaluatedTableName`.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheLastEvaluatedTableNameNullOnTheLastPage(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"TableNames":["Forum"]}'));

        $response = $this->createClient($httpClient)->listTables();

        self::assertSame(['Forum'], $response->tableNames->toArray());
        self::assertNull($response->lastEvaluatedTableName);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsAnEmptyListWhenTheServiceOmitsTheTableNames(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->listTables();

        self::assertTrue($response->tableNames->isEmpty());
        self::assertNull($response->lastEvaluatedTableName);
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
        $httpClient->addResponse(new Response(body: '{"TableNames":["Forum",42]}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->listTables();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnExclusiveStartTableNameShorterThanThreeCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListTablesRequest(exclusiveStartTableName: 'Fo');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnExclusiveStartTableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListTablesRequest(exclusiveStartTableName: str_repeat('a', 256));
    }

    /**
     * The start is a table name; an ARN is not accepted.
     *
     * @throws InvalidArgumentException
     */
    public function testRejectsAnExclusiveStartTableNameWithACharacterThePatternDoesNotAllow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListTablesRequest(exclusiveStartTableName: 'arn:aws:dynamodb:eu-central-1:123456789012:table/Forum');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsALimitAboveOneHundred(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListTablesRequest(limit: 101);
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
