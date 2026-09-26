<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use DateTimeImmutable;
use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\ListBackupsRequest;
use Imper86\DynamoDBClient\Model\BackupStatus;
use Imper86\DynamoDBClient\Model\BackupSummary;
use Imper86\DynamoDBClient\Model\BackupType;
use Imper86\DynamoDBClient\Model\BackupTypeFilter;
use Imper86\DynamoDBClient\Model\Credentials;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The ListBackups reference has no Examples section, so the fixtures are built from its request and
 * response syntax: the second page of every backup of a `Music` table taken in December 2019, holding
 * one USER backup and the SYSTEM backup DynamoDB kept when the table was deleted.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_ListBackups.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class ListBackupsTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/list-backups-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/list-backups-response.json';

    private const LAST_EVALUATED_BACKUP_ARN =
        'arn:aws:dynamodb:eu-central-1:123456789012:table/Music/backup/01576702766500-d6a7af5c';

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

        $this->createClient($httpClient)->listBackups(new ListBackupsRequest(
            backupType: BackupTypeFilter::ALL,
            exclusiveStartBackupArn: 'arn:aws:dynamodb:eu-central-1:123456789012:table/Music/backup/01576616366715-b4e58d3a',
            limit: 2,
            tableName: 'Music',
            timeRangeLowerBound: new DateTimeImmutable('2019-12-01T00:00:00Z'),
            timeRangeUpperBound: new DateTimeImmutable('2020-01-01T00:00:00Z'),
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.ListBackups', $sent->getHeaderLine('X-Amz-Target'));
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

        $this->createClient($httpClient)->listBackups();

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
    public function testReturnsTheSummaryOfEveryBackup(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->listBackups();

        self::assertCount(2, $response->backupSummaries);

        $backup = $response->backupSummaries->get(0);

        self::assertInstanceOf(BackupSummary::class, $backup);
        self::assertSame(
            'arn:aws:dynamodb:eu-central-1:123456789012:table/Music/backup/01576616366715-c5f69e4b',
            $backup->backupArn,
        );
        self::assertSame('MusicBackup', $backup->backupName);
        self::assertSame(2048, $backup->backupSizeBytes);
        self::assertSame(BackupStatus::AVAILABLE, $backup->backupStatus);
        self::assertSame(BackupType::USER, $backup->backupType);
        self::assertSame('arn:aws:dynamodb:eu-central-1:123456789012:table/Music', $backup->tableArn);
        self::assertSame('e0a1b2c3-d4e5-4f60-8a7b-9c8d7e6f5a4b', $backup->tableId);
        self::assertSame('Music', $backup->tableName);
        self::assertNull($backup->backupExpiryDateTime);

        $createdAt = $backup->backupCreationDateTime;

        self::assertInstanceOf(DateTimeImmutable::class, $createdAt);
        self::assertSame('2019-12-17T20:59:26.715000+00:00', $createdAt->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * A SYSTEM backup carries its expiry, and a name outside the pattern a caller may choose.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheExpiryOfASystemBackup(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $backup = $this->createClient($httpClient)->listBackups()->backupSummaries->get(1);

        self::assertInstanceOf(BackupSummary::class, $backup);
        self::assertSame(BackupType::SYSTEM, $backup->backupType);
        self::assertSame('Music$DeletedTableBackup', $backup->backupName);

        $expiresAt = $backup->backupExpiryDateTime;

        self::assertInstanceOf(DateTimeImmutable::class, $expiresAt);
        self::assertSame('2020-01-22T20:59:26.500000+00:00', $expiresAt->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsWhereTheNextPageStarts(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->listBackups();

        self::assertSame(self::LAST_EVALUATED_BACKUP_ARN, $response->lastEvaluatedBackupArn);
    }

    /**
     * The last page has no `LastEvaluatedBackupArn`.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheElementsTheServiceOmitsNull(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"BackupSummaries":[{"BackupName":"MusicBackup"}]}'));

        $response = $this->createClient($httpClient)->listBackups();

        self::assertNull($response->lastEvaluatedBackupArn);

        $backup = $response->backupSummaries->get(0);

        self::assertInstanceOf(BackupSummary::class, $backup);
        self::assertSame('MusicBackup', $backup->backupName);
        self::assertNull($backup->backupArn);
        self::assertNull($backup->backupCreationDateTime);
        self::assertNull($backup->backupSizeBytes);
        self::assertNull($backup->backupStatus);
        self::assertNull($backup->backupType);
        self::assertNull($backup->tableArn);
        self::assertNull($backup->tableId);
        self::assertNull($backup->tableName);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsAnEmptyListWhenTheServiceOmitsTheBackupSummaries(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->listBackups();

        self::assertTrue($response->backupSummaries->isEmpty());
        self::assertNull($response->lastEvaluatedBackupArn);
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
        $httpClient->addResponse(new Response(body: '{"BackupSummaries":[{"BackupType":"ALL"}]}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->listBackups();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnExclusiveStartBackupArnShorterThanThirtySevenCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListBackupsRequest(exclusiveStartBackupArn: str_repeat('a', 36));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnExclusiveStartBackupArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListBackupsRequest(exclusiveStartBackupArn: str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsALimitAboveOneHundred(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListBackupsRequest(limit: 101);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListBackupsRequest(tableName: str_repeat('a', 1025));
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
