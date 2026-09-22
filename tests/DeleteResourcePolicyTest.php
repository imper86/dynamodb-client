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
use Imper86\DynamoDBClient\Message\DeleteResourcePolicyRequest;
use Imper86\DynamoDBClient\Model\Credentials;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The messages exchanged here are the "Delete the resource-based policy of a table" example of the
 * DeleteResourcePolicy reference.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_DeleteResourcePolicy.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class DeleteResourcePolicyTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/delete-resource-policy-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/delete-resource-policy-response.json';

    private const string TABLE_ARN = 'arn:aws:dynamodb:us-west-2:123456789012:table/Thread';

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

        $this->createClient($httpClient)->deleteResourcePolicy(new DeleteResourcePolicyRequest(self::TABLE_ARN));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.DeleteResourcePolicy', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSendsTheExpectedRevisionOfAConditionalDelete(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $this->createClient($httpClient)->deleteResourcePolicy(new DeleteResourcePolicyRequest(
            resourceArn: self::TABLE_ARN,
            expectedRevisionId: '1683717331354',
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertJsonStringEqualsJsonString(
            '{"ResourceArn":"' . self::TABLE_ARN . '","ExpectedRevisionId":"1683717331354"}',
            $sent->getBody()->__toString(),
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheRevisionOfTheDeletedPolicy(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)
            ->deleteResourcePolicy(new DeleteResourcePolicyRequest(self::TABLE_ARN))
        ;

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

        $response = $this->createClient($httpClient)
            ->deleteResourcePolicy(new DeleteResourcePolicyRequest(self::TABLE_ARN))
        ;

        self::assertNull($response->revisionId);
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
        $httpClient->addResponse(new Response(body: '{"RevisionId":["1683717331354"]}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->deleteResourcePolicy(new DeleteResourcePolicyRequest(self::TABLE_ARN));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAResourceArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DeleteResourcePolicyRequest(str_repeat('a', 1284));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnExpectedRevisionIdLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DeleteResourcePolicyRequest(resourceArn: self::TABLE_ARN, expectedRevisionId: str_repeat('1', 256));
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
