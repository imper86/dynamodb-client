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
use Imper86\DynamoDBClient\Message\PutResourcePolicyRequest;
use Imper86\DynamoDBClient\Model\Credentials;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The messages exchanged here are the "Attach a resource-based policy to a table" example of the
 * PutResourcePolicy reference.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_PutResourcePolicy.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class PutResourcePolicyTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/put-resource-policy-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/put-resource-policy-response.json';

    private const TABLE_ARN = 'arn:aws:dynamodb:us-west-2:123456789012:table/Thread';

    private const POLICY = '{"Version":"2012-10-17","Statement":{"Effect":"Allow","Principal":{"AWS":['
        . '"arn:aws:iam::111122223333:root","arn:aws:iam::444455556666:root"]},"Action":["dynamodb:GetItem"],'
        . '"Resource":"arn:aws:dynamodb:us-west-2:123456789012:table/Thread"}}';

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

        $this->createClient($httpClient)->putResourcePolicy($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.PutResourcePolicy', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSendsEveryOptionalParameter(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $this->createClient($httpClient)->putResourcePolicy(new PutResourcePolicyRequest(
            policy: '{}',
            resourceArn: self::TABLE_ARN,
            confirmRemoveSelfResourceAccess: true,
            expectedRevisionId: 'NO_POLICY',
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertJsonStringEqualsJsonString(
            '{"Policy":"{}","ResourceArn":"' . self::TABLE_ARN . '",'
            . '"ConfirmRemoveSelfResourceAccess":true,"ExpectedRevisionId":"NO_POLICY"}',
            $sent->getBody()->__toString(),
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheRevisionOfTheAttachedPolicy(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->putResourcePolicy($this->documentedRequest());

        self::assertSame('1683717331354', $response->revisionId);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheRevisionNullWhenTheServiceOmitsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->putResourcePolicy($this->documentedRequest());

        self::assertNull($response->revisionId);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testWrapsAResponseItCannotDeserialize(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"RevisionId":["1683717331354"]}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->putResourcePolicy($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAResourceArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PutResourcePolicyRequest(policy: self::POLICY, resourceArn: str_repeat('a', 1284));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnExpectedRevisionIdLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PutResourcePolicyRequest(
            policy: self::POLICY,
            resourceArn: self::TABLE_ARN,
            expectedRevisionId: str_repeat('1', 256),
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

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): PutResourcePolicyRequest
    {
        return new PutResourcePolicyRequest(policy: self::POLICY, resourceArn: self::TABLE_ARN);
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
