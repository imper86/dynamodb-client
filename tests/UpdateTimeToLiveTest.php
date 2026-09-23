<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\UpdateTimeToLiveRequest;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\TimeToLiveSpecification;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The UpdateTimeToLive reference has no Examples section, so the fixtures are built from its request and
 * response syntax, enabling Time to Live on the `ExpiresAt` attribute of a `Music` table.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_UpdateTimeToLive.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class UpdateTimeToLiveTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/update-time-to-live-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/update-time-to-live-response.json';

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

        $this->createClient($httpClient)->updateTimeToLive($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.UpdateTimeToLive', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheNewSpecification(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->updateTimeToLive($this->documentedRequest());

        $specification = $response->timeToLiveSpecification;

        self::assertInstanceOf(TimeToLiveSpecification::class, $specification);
        self::assertSame('ExpiresAt', $specification->attributeName);
        self::assertTrue($specification->enabled);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheSpecificationNullWhenTheServiceOmitsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->updateTimeToLive($this->documentedRequest());

        self::assertNull($response->timeToLiveSpecification);
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
        $httpClient->addResponse(new Response(body: '{"TimeToLiveSpecification":"ExpiresAt"}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->updateTimeToLive($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UpdateTimeToLiveRequest::enable(str_repeat('a', 1025), 'ExpiresAt');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnAttributeNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UpdateTimeToLiveRequest::enable('Music', str_repeat('a', 256));
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): UpdateTimeToLiveRequest
    {
        return UpdateTimeToLiveRequest::enable('Music', 'ExpiresAt');
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
