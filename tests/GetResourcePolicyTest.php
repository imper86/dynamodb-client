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
use Imper86\DynamoDBClient\Message\GetResourcePolicyRequest;
use Imper86\DynamoDBClient\Model\Credentials;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function json_decode;
use function str_repeat;

use const JSON_THROW_ON_ERROR;

/**
 * The messages exchanged here are the "Get the resource-based policy of a table" example of the
 * GetResourcePolicy reference.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_GetResourcePolicy.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class GetResourcePolicyTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/get-resource-policy-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/get-resource-policy-response.json';

    private const TABLE_ARN = 'arn:aws:dynamodb:us-west-2:123456789012:table/Thread';

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

        $this->createClient($httpClient)->getResourcePolicy(new GetResourcePolicyRequest(self::TABLE_ARN));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.GetResourcePolicy', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     * @throws JsonException
     */
    public function testReturnsThePolicyDocumentAsTheServiceSentIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->getResourcePolicy(new GetResourcePolicyRequest(self::TABLE_ARN));

        $policy = $response->policy;

        self::assertIsString($policy);
        self::assertSame(
            [
                'Version' => '2012-10-17',
                'Statement' => [
                    'Effect' => 'Allow',
                    'Principal' => ['AWS' => ['arn:aws:iam::111122223333:root', 'arn:aws:iam::444455556666:root']],
                    'Action' => ['dynamodb:GetItem'],
                    'Resource' => self::TABLE_ARN,
                ],
            ],
            json_decode($policy, true, flags: JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheRevisionOfThePolicy(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->getResourcePolicy(new GetResourcePolicyRequest(self::TABLE_ARN));

        self::assertSame('1683717331354', $response->revisionId);
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
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->getResourcePolicy(new GetResourcePolicyRequest(self::TABLE_ARN));

        self::assertNull($response->policy);
        self::assertNull($response->revisionId);
    }

    /**
     * The policy arrives as a JSON string; an object in its place cannot become one.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testFailsOnABodyItCannotDeserialize(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Policy":{"Version":"2012-10-17"}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->getResourcePolicy(new GetResourcePolicyRequest(self::TABLE_ARN));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAResourceArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetResourcePolicyRequest(str_repeat('a', 1284));
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
