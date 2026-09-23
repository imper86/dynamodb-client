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
use Imper86\DynamoDBClient\Message\UpdateContinuousBackupsRequest;
use Imper86\DynamoDBClient\Model\ContinuousBackupsDescription;
use Imper86\DynamoDBClient\Model\ContinuousBackupsStatus;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\PointInTimeRecoveryDescription;
use Imper86\DynamoDBClient\Model\PointInTimeRecoverySpecification;
use Imper86\DynamoDBClient\Model\PointInTimeRecoveryStatus;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The UpdateContinuousBackups reference has no Examples section, so the fixtures are built from its
 * request and response syntax, enabling point in time recovery on a `Music` table over a 7-day period.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_UpdateContinuousBackups.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class UpdateContinuousBackupsTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/update-continuous-backups-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/update-continuous-backups-response.json';

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

        $this->createClient($httpClient)->updateContinuousBackups($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.UpdateContinuousBackups', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheUpdatedPointInTimeRecoverySettings(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->updateContinuousBackups($this->documentedRequest());

        $description = $response->continuousBackupsDescription;

        self::assertInstanceOf(ContinuousBackupsDescription::class, $description);
        self::assertSame(ContinuousBackupsStatus::ENABLED, $description->continuousBackupsStatus);

        $recovery = $description->pointInTimeRecoveryDescription;

        self::assertInstanceOf(PointInTimeRecoveryDescription::class, $recovery);
        self::assertSame(PointInTimeRecoveryStatus::ENABLED, $recovery->pointInTimeRecoveryStatus);
        self::assertSame(7, $recovery->recoveryPeriodInDays);

        $latest = $recovery->latestRestorableDateTime;

        self::assertInstanceOf(DateTimeImmutable::class, $latest);
        self::assertSame('2019-12-17T23:07:46.799000+00:00', $latest->format('Y-m-d\TH:i:s.uP'));
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
            new Response(body: '{"ContinuousBackupsDescription":{"ContinuousBackupsStatus":"ENABLED"}}'),
        );

        $response = $this->createClient($httpClient)->updateContinuousBackups($this->documentedRequest());

        $description = $response->continuousBackupsDescription;

        self::assertInstanceOf(ContinuousBackupsDescription::class, $description);
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

        $response = $this->createClient($httpClient)->updateContinuousBackups($this->documentedRequest());

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

        $this->createClient($httpClient)->updateContinuousBackups($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UpdateContinuousBackupsRequest::disable(str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsARecoveryPeriodLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PointInTimeRecoverySpecification(true, 36);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): UpdateContinuousBackupsRequest
    {
        return UpdateContinuousBackupsRequest::enable('Music', 7);
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
