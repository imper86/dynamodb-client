<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\BadResponseException;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Message\TagResourceRequest;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\TagList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function str_repeat;

/**
 * The TagResource reference has no Examples section, so the request fixture is built from its request
 * syntax, with two tags for a table named `Music`. The service answers with an empty body, so there is no
 * response fixture.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_TagResource.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class TagResourceTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/tag-resource-request.json';

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSendsTheRequestTheWayTheApiReferenceDocumentsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response());

        $this->createClient($httpClient)->tagResource($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.TagResource', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * There is nothing to deserialize, so even a body that is not JSON cannot fail the call.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testIgnoresTheBodyOfASuccessfulResponse(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: 'not json'));

        $this->createClient($httpClient)->tagResource($this->documentedRequest());

        self::assertCount(1, $httpClient->getRequests());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testWrapsAResponseTheServiceRejected(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(
            400,
            body: '{"__type":"com.amazonaws.dynamodb.v20120810#ResourceNotFoundException",'
            . '"message":"Requested resource not found"}',
        ));

        try {
            $this->createClient($httpClient)->tagResource($this->documentedRequest());
            self::fail('Expected a ' . BadResponseException::class . '.');
        } catch (BadResponseException $exception) {
            self::assertSame(400, $exception->response->getStatusCode());
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAResourceArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TagResourceRequest(resourceArn: str_repeat('a', 1284), tags: new TagList());
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
    private function documentedRequest(): TagResourceRequest
    {
        return TagResourceRequest::tags(
            'arn:aws:dynamodb:eu-central-1:123456789012:table/Music',
            ['Environment' => 'production', 'Owner' => 'music-team'],
        );
    }
}
