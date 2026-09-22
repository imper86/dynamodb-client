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
use Imper86\DynamoDBClient\Message\DescribeBackupRequest;
use Imper86\DynamoDBClient\Model\BackupDescription;
use Imper86\DynamoDBClient\Model\BackupDetails;
use Imper86\DynamoDBClient\Model\BackupStatus;
use Imper86\DynamoDBClient\Model\BackupType;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\SourceTableDetails;
use Imper86\DynamoDBClient\Model\SourceTableFeatureDetails;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The DescribeBackup reference has no Examples section, so the fixtures are built from its request and
 * response syntax, describing an available backup of the `Music` table that carried every feature the
 * response can report. The response shape is the one DeleteBackup returns, whose test covers the
 * nested source table models in depth.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_DescribeBackup.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class DescribeBackupTest extends TestCase
{
    private const string BACKUP_ARN =
        'arn:aws:dynamodb:eu-central-1:123456789012:table/Music/backup/01576624066799-c3f0dcd7';

    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/describe-backup-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/describe-backup-response.json';

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

        $this->createClient($httpClient)->describeBackup($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.DescribeBackup', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheDetailsOfTheBackup(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->describeBackup($this->documentedRequest());

        $description = $response->backupDescription;

        self::assertInstanceOf(BackupDescription::class, $description);

        $details = $description->backupDetails;

        self::assertInstanceOf(BackupDetails::class, $details);
        self::assertSame(self::BACKUP_ARN, $details->backupArn);
        self::assertSame('MusicBackup', $details->backupName);
        self::assertSame(123456, $details->backupSizeBytes);
        self::assertSame(BackupStatus::AVAILABLE, $details->backupStatus);
        self::assertSame(BackupType::USER, $details->backupType);

        $createdAt = $details->backupCreationDateTime;

        self::assertInstanceOf(DateTimeImmutable::class, $createdAt);
        self::assertSame('2019-12-17T23:07:46.799000+00:00', $createdAt->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheTableTheBackupWasTakenOf(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->describeBackup($this->documentedRequest());

        $description = $response->backupDescription;

        self::assertInstanceOf(BackupDescription::class, $description);

        $source = $description->sourceTableDetails;

        self::assertInstanceOf(SourceTableDetails::class, $source);
        self::assertSame('Music', $source->tableName);
        self::assertSame('arn:aws:dynamodb:eu-central-1:123456789012:table/Music', $source->tableArn);
        self::assertCount(2, $source->keySchema ?? []);

        $features = $description->sourceTableFeatureDetails;

        self::assertInstanceOf(SourceTableFeatureDetails::class, $features);
        self::assertCount(1, $features->globalSecondaryIndexes ?? []);
        self::assertCount(1, $features->localSecondaryIndexes ?? []);
        self::assertCount(1, $features->vectorIndexes ?? []);
        self::assertSame('ExpiresAt', $features->timeToLiveDescription?->attributeName);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheSourceTableElementsNullWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"BackupDescription":{"BackupDetails":{'
            . '"BackupArn":"' . self::BACKUP_ARN . '","BackupCreationDateTime":1576624066.799,'
            . '"BackupName":"MusicBackup","BackupStatus":"CREATING","BackupType":"USER"}}}'));

        $response = $this->createClient($httpClient)->describeBackup($this->documentedRequest());

        $description = $response->backupDescription;

        self::assertInstanceOf(BackupDescription::class, $description);
        self::assertSame(BackupStatus::CREATING, $description->backupDetails?->backupStatus);
        self::assertNull($description->sourceTableDetails);
        self::assertNull($description->sourceTableFeatureDetails);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheBackupDescriptionNullWhenTheServiceOmitsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->describeBackup($this->documentedRequest());

        self::assertNull($response->backupDescription);
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
        $httpClient->addResponse(
            new Response(body: '{"BackupDescription":{"BackupDetails":{"BackupStatus":"RESTORING"}}}'),
        );

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->describeBackup($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsABackupArnShorterThanThirtySevenCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DescribeBackupRequest(backupArn: str_repeat('a', 36));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsABackupArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DescribeBackupRequest(backupArn: str_repeat('a', 1025));
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
    private function documentedRequest(): DescribeBackupRequest
    {
        return new DescribeBackupRequest(backupArn: self::BACKUP_ARN);
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
