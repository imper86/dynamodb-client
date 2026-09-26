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
use Imper86\DynamoDBClient\Message\DescribeContinuousBackupsRequest;
use Imper86\DynamoDBClient\Model\ContinuousBackupsDescription;
use Imper86\DynamoDBClient\Model\ContinuousBackupsStatus;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\PointInTimeRecoveryDescription;
use Imper86\DynamoDBClient\Model\PointInTimeRecoveryStatus;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The DescribeContinuousBackups reference has no Examples section, so the fixtures are built from its
 * request and response syntax, describing a `Music` table with point in time recovery enabled over the
 * full 35-day period.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_DescribeContinuousBackups.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class DescribeContinuousBackupsTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/describe-continuous-backups-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/describe-continuous-backups-response.json';

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

        $this->createClient($httpClient)->describeContinuousBackups(new DescribeContinuousBackupsRequest('Music'));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.DescribeContinuousBackups', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsThePointInTimeRecoverySettings(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)
            ->describeContinuousBackups(new DescribeContinuousBackupsRequest('Music'))
        ;

        $description = $response->continuousBackupsDescription;

        self::assertInstanceOf(ContinuousBackupsDescription::class, $description);
        self::assertSame(ContinuousBackupsStatus::ENABLED, $description->continuousBackupsStatus);

        $recovery = $description->pointInTimeRecoveryDescription;

        self::assertInstanceOf(PointInTimeRecoveryDescription::class, $recovery);
        self::assertSame(PointInTimeRecoveryStatus::ENABLED, $recovery->pointInTimeRecoveryStatus);
        self::assertSame(35, $recovery->recoveryPeriodInDays);

        $earliest = $recovery->earliestRestorableDateTime;

        self::assertInstanceOf(DateTimeImmutable::class, $earliest);
        self::assertSame('2019-12-17T22:50:00.500000+00:00', $earliest->format('Y-m-d\TH:i:s.uP'));

        $latest = $recovery->latestRestorableDateTime;

        self::assertInstanceOf(DateTimeImmutable::class, $latest);
        self::assertSame('2019-12-17T23:07:46.799000+00:00', $latest->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * With point in time recovery disabled, there is no restorable window to report.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheRestorableWindowNullWhenRecoveryIsDisabled(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"ContinuousBackupsDescription":{'
            . '"ContinuousBackupsStatus":"ENABLED",'
            . '"PointInTimeRecoveryDescription":{"PointInTimeRecoveryStatus":"DISABLED"}}}'));

        $response = $this->createClient($httpClient)
            ->describeContinuousBackups(new DescribeContinuousBackupsRequest('Music'))
        ;

        $recovery = $response->continuousBackupsDescription?->pointInTimeRecoveryDescription;

        self::assertInstanceOf(PointInTimeRecoveryDescription::class, $recovery);
        self::assertSame(PointInTimeRecoveryStatus::DISABLED, $recovery->pointInTimeRecoveryStatus);
        self::assertNull($recovery->earliestRestorableDateTime);
        self::assertNull($recovery->latestRestorableDateTime);
        self::assertNull($recovery->recoveryPeriodInDays);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesThePointInTimeRecoveryDescriptionNullWhenTheServiceOmitsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(
            new Response(body: '{"ContinuousBackupsDescription":{"ContinuousBackupsStatus":"DISABLED"}}'),
        );

        $response = $this->createClient($httpClient)
            ->describeContinuousBackups(new DescribeContinuousBackupsRequest('Music'))
        ;

        $description = $response->continuousBackupsDescription;

        self::assertInstanceOf(ContinuousBackupsDescription::class, $description);
        self::assertSame(ContinuousBackupsStatus::DISABLED, $description->continuousBackupsStatus);
        self::assertNull($description->pointInTimeRecoveryDescription);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheContinuousBackupsDescriptionNullWhenTheServiceOmitsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)
            ->describeContinuousBackups(new DescribeContinuousBackupsRequest('Music'))
        ;

        self::assertNull($response->continuousBackupsDescription);
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
            new Response(body: '{"ContinuousBackupsDescription":{"ContinuousBackupsStatus":"PAUSED"}}'),
        );

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->describeContinuousBackups(new DescribeContinuousBackupsRequest('Music'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DescribeContinuousBackupsRequest(str_repeat('a', 1025));
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
