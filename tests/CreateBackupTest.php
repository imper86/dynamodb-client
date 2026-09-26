<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use DateTimeImmutable;
use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\CreateBackupRequest;
use Imper86\DynamoDBClient\Model\BackupDetails;
use Imper86\DynamoDBClient\Model\BackupStatus;
use Imper86\DynamoDBClient\Model\BackupType;
use Imper86\DynamoDBClient\Model\Credentials;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The CreateBackup reference has no Examples section, so the fixtures are built from its request and
 * response syntax, with the values an on-demand backup of a table named `Music` would come back with.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_CreateBackup.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class CreateBackupTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/create-backup-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/create-backup-response.json';

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

        $this->createClient($httpClient)->createBackup($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('POST', $sent->getMethod());
        self::assertSame('https://dynamodb.eu-central-1.amazonaws.com/', $sent->getUri()->__toString());
        self::assertSame('DynamoDB_20120810.CreateBackup', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheDetailsOfTheBackupItStarted(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->createBackup($this->documentedRequest());

        $details = $response->backupDetails;

        self::assertInstanceOf(BackupDetails::class, $details);
        self::assertSame(
            'arn:aws:dynamodb:eu-central-1:123456789012:table/Music/backup/01576624066799-c3f0dcd7',
            $details->backupArn,
        );
        self::assertSame('MusicBackup', $details->backupName);
        self::assertSame(0, $details->backupSizeBytes);
        self::assertSame(BackupStatus::CREATING, $details->backupStatus);
        self::assertSame(BackupType::USER, $details->backupType);
    }

    /**
     * The service sends a timestamp as epoch seconds with a fractional part, not as a date string.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReadsTheCreationTimeAsAPointInTime(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->createBackup($this->documentedRequest());

        $createdAt = $response->backupDetails?->backupCreationDateTime;

        self::assertInstanceOf(DateTimeImmutable::class, $createdAt);
        self::assertSame('2019-12-17T23:07:46.799000+00:00', $createdAt->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * A USER backup never expires, so the element only shows up on a SYSTEM backup.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReadsTheExpiryTimeOfASystemBackup(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"BackupDetails":{"BackupArn":"arn:backup",'
            . '"BackupCreationDateTime":1576624066.799,"BackupExpiryDateTime":1579734000,'
            . '"BackupName":"MusicBackup","BackupStatus":"AVAILABLE","BackupType":"SYSTEM"}}'));

        $response = $this->createClient($httpClient)->createBackup($this->documentedRequest());

        $details = $response->backupDetails;

        self::assertInstanceOf(BackupDetails::class, $details);
        self::assertSame(BackupType::SYSTEM, $details->backupType);
        self::assertSame(BackupStatus::AVAILABLE, $details->backupStatus);
        self::assertInstanceOf(DateTimeImmutable::class, $details->backupExpiryDateTime);
        self::assertSame('2020-01-22T23:00:00.000000+00:00', $details->backupExpiryDateTime->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheOptionalDetailsNullWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"BackupDetails":{"BackupArn":"arn:backup",'
            . '"BackupCreationDateTime":1576624066.799,"BackupName":"MusicBackup",'
            . '"BackupStatus":"CREATING","BackupType":"USER"}}'));

        $response = $this->createClient($httpClient)->createBackup($this->documentedRequest());

        $details = $response->backupDetails;

        self::assertInstanceOf(BackupDetails::class, $details);
        self::assertNull($details->backupExpiryDateTime);
        self::assertNull($details->backupSizeBytes);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheBackupDetailsNullWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->createBackup($this->documentedRequest());

        self::assertNull($response->backupDetails);
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
        $httpClient->addResponse(new Response(body: '{"BackupDetails":{"BackupStatus":"RESTORING"}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->createBackup($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsABackupNameShorterThanThreeCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CreateBackupRequest(backupName: 'Mu', tableName: 'Music');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsABackupNameLongerThanTwoHundredAndFiftyFiveCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CreateBackupRequest(backupName: str_repeat('a', 256), tableName: 'Music');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsABackupNameWithACharacterThePatternDoesNotAllow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CreateBackupRequest(backupName: 'Music Backup', tableName: 'Music');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testAcceptsABackupNameOfDotsDashesAndUnderscores(): void
    {
        $request = new CreateBackupRequest(backupName: 'Music_backup-2019.12.17', tableName: 'Music');

        self::assertSame('Music_backup-2019.12.17', $request->backupName);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CreateBackupRequest(backupName: 'MusicBackup', tableName: str_repeat('a', 1025));
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
    private function documentedRequest(): CreateBackupRequest
    {
        return new CreateBackupRequest(backupName: 'MusicBackup', tableName: 'Music');
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
