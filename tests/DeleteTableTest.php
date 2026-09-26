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
use Imper86\DynamoDBClient\Message\DeleteTableRequest;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\ProvisionedThroughputDescription;
use Imper86\DynamoDBClient\Model\TableDescription;
use Imper86\DynamoDBClient\Model\TableStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The messages exchanged here are the "Delete a Table" example of the DeleteTable reference.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_DeleteTable.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class DeleteTableTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/delete-table-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/delete-table-response.json';

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

        $this->createClient($httpClient)->deleteTable(new DeleteTableRequest('Reply'));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.DeleteTable', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheDescriptionOfTheTableBeingDeleted(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->deleteTable(new DeleteTableRequest('Reply'));

        $table = $response->tableDescription;

        self::assertInstanceOf(TableDescription::class, $table);
        self::assertSame('arn:aws:dynamodb:us-west-2:123456789012:table/Reply', $table->tableArn);
        self::assertSame('Reply', $table->tableName);
        self::assertSame(TableStatus::DELETING, $table->tableStatus);
        self::assertSame(0, $table->itemCount);
        self::assertSame(0, $table->tableSizeBytes);

        $throughput = $table->provisionedThroughput;

        self::assertInstanceOf(ProvisionedThroughputDescription::class, $throughput);
        self::assertSame(0, $throughput->numberOfDecreasesToday);
        self::assertSame(5, $throughput->readCapacityUnits);
        self::assertSame(5, $throughput->writeCapacityUnits);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheElementsTheServiceOmitsNull(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->deleteTable(new DeleteTableRequest('Reply'));

        $table = $response->tableDescription;

        self::assertInstanceOf(TableDescription::class, $table);
        self::assertNull($table->keySchema);
        self::assertNull($table->attributeDefinitions);
        self::assertNull($table->creationDateTime);
        self::assertNull($table->billingModeSummary);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheTableDescriptionNullWhenTheServiceOmitsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->deleteTable(new DeleteTableRequest('Reply'));

        self::assertNull($response->tableDescription);
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
        $httpClient->addResponse(new Response(body: '{"TableDescription":{"TableStatus":"GONE"}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->deleteTable(new DeleteTableRequest('Reply'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DeleteTableRequest(str_repeat('a', 1025));
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
