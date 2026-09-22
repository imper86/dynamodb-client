<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\ListTagsOfResourceRequest;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\Tag;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function array_map;
use function file_get_contents;
use function str_repeat;

/**
 * The ListTagsOfResource reference has no Examples section, so the fixtures are built from its request
 * and response syntax: a page of the tags of a `Music` table, one of them with an empty value.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_ListTagsOfResource.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class ListTagsOfResourceTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/list-tags-of-resource-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/list-tags-of-resource-response.json';

    private const string TABLE_ARN = 'arn:aws:dynamodb:eu-central-1:123456789012:table/Music';

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

        $this->createClient($httpClient)->listTagsOfResource(
            new ListTagsOfResourceRequest(self::TABLE_ARN, nextToken: 'eyJLZXkiOiJFbnZpcm9ubWVudCJ9'),
        );

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.ListTagsOfResource', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheTagsOfTheResource(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $tags = $this->createClient($httpClient)
            ->listTagsOfResource(new ListTagsOfResourceRequest(self::TABLE_ARN))
            ->tags
        ;

        self::assertSame(
            [['Environment', 'production'], ['Team', '']],
            array_map(static fn(Tag $tag): array => [$tag->key, $tag->value], $tags->toArray()),
        );
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

        $response = $this->createClient($httpClient)->listTagsOfResource(new ListTagsOfResourceRequest(self::TABLE_ARN));

        self::assertSame('eyJLZXkiOiJUZWFtIn0', $response->nextToken);
    }

    /**
     * The last page has no `NextToken`.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheNextTokenNullOnTheLastPage(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Tags":[{"Key":"Environment","Value":"production"}]}'));

        $response = $this->createClient($httpClient)->listTagsOfResource(new ListTagsOfResourceRequest(self::TABLE_ARN));

        self::assertCount(1, $response->tags);
        self::assertNull($response->nextToken);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsAnEmptyListWhenTheServiceOmitsTheTags(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->listTagsOfResource(new ListTagsOfResourceRequest(self::TABLE_ARN));

        self::assertTrue($response->tags->isEmpty());
        self::assertNull($response->nextToken);
    }

    /**
     * A tag without its value cannot be built.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testFailsOnABodyItCannotDeserialize(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Tags":[{"Key":"Environment"}]}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->listTagsOfResource(new ListTagsOfResourceRequest(self::TABLE_ARN));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAResourceArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListTagsOfResourceRequest(str_repeat('a', 1284));
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
