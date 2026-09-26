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
use Imper86\DynamoDBClient\Message\SearchVectorsRequest;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueList;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\SearchResultItem;
use Imper86\DynamoDBClient\Model\SearchResultItemList;
use Imper86\DynamoDBClient\Model\VectorCapacity;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function array_fill;
use function file_get_contents;
use function str_repeat;

/**
 * The SearchVectors reference has no Examples section, so the fixtures are built from its request and
 * response syntax, with the values a three-dimensional search of a `LyricsIndex` on the `Music` table
 * would come back with.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_SearchVectors.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class SearchVectorsTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/search-vectors-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/search-vectors-response.json';

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

        $this->createClient($httpClient)->searchVectors($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.SearchVectors', $sent->getHeaderLine('X-Amz-Target'));
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
        $httpClient->addResponse(new Response(body: '{}'));

        $this->createClient($httpClient)->searchVectors(new SearchVectorsRequest(
            indexName: 'LyricsIndex',
            searchVector: new AttributeValueList([AttributeValue::number('0.12')]),
            tableName: 'Music',
            topK: 5,
            expressionAttributeNames: new NonEmptyStringMap(['#A' => 'Artist']),
            expressionAttributeValues: new AttributeValueMap([':a' => AttributeValue::string('Acme Band')]),
            projectionExpression: 'SongTitle',
            returnConsumedCapacity: ReturnConsumedCapacity::INDEXES,
            searchConditionExpression: '#A = :a',
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertJsonStringEqualsJsonString(
            '{"IndexName":"LyricsIndex","SearchVector":[{"N":"0.12"}],"TableName":"Music","TopK":5,'
            . '"ExpressionAttributeNames":{"#A":"Artist"},"ExpressionAttributeValues":{":a":{"S":"Acme Band"}},'
            . '"ProjectionExpression":"SongTitle","ReturnConsumedCapacity":"INDEXES",'
            . '"SearchConditionExpression":"#A = :a"}',
            $sent->getBody()->__toString(),
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheMostSimilarItemsFirst(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->searchVectors($this->documentedRequest());

        self::assertCount(2, $response->searchResults);

        $first = $response->searchResults->get(0);

        self::assertInstanceOf(SearchResultItem::class, $first);
        self::assertSame(0.0421, $first->score);
        self::assertSame('Call Me Today', $first->item?->get('SongTitle')?->string);

        $second = $response->searchResults->get(1);

        self::assertInstanceOf(SearchResultItem::class, $second);
        self::assertSame(0.3187, $second->score);
        self::assertSame('Happy Day', $second->item?->get('SongTitle')?->string);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheConsumedCapacity(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->searchVectors($this->documentedRequest());

        $consumedCapacity = $response->consumedCapacity;

        self::assertInstanceOf(VectorCapacity::class, $consumedCapacity);
        self::assertSame(12.0, $consumedCapacity->vectorSearchRequestBytes);
        self::assertNull($consumedCapacity->vectorWriteRequestBytes);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testDefaultsToNoResultsWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->searchVectors($this->documentedRequest());

        self::assertTrue($response->searchResults->isEmpty());
        self::assertNull($response->consumedCapacity);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheMembersOfAResultTheServiceOmitsNull(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"SearchResults":[{}]}'));

        $response = $this->createClient($httpClient)->searchVectors($this->documentedRequest());

        $result = $response->searchResults->get(0);

        self::assertInstanceOf(SearchResultItem::class, $result);
        self::assertNull($result->item);
        self::assertNull($result->score);
    }

    /**
     * Search results that are a JSON object cannot become a {@see SearchResultItemList}.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testWrapsAResponseItCannotDeserialize(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"SearchResults":{"Score":0.0421}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->searchVectors($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameShorterThanThreeCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SearchVectorsRequest(indexName: 'Ly', searchVector: $this->searchVector(), tableName: 'Music', topK: 2);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameLongerThanTwoHundredAndFiftyFiveCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SearchVectorsRequest(
            indexName: str_repeat('a', 256),
            searchVector: $this->searchVector(),
            tableName: 'Music',
            topK: 2,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameWithACharacterThePatternDoesNotAllow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SearchVectorsRequest(
            indexName: 'Lyrics Index',
            searchVector: $this->searchVector(),
            tableName: 'Music',
            topK: 2,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptySearchVector(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SearchVectorsRequest(
            indexName: 'LyricsIndex',
            searchVector: new AttributeValueList(),
            tableName: 'Music',
            topK: 2,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsASearchVectorOfMoreThanFourThousandAndNinetySixDimensions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SearchVectorsRequest(
            indexName: 'LyricsIndex',
            searchVector: new AttributeValueList(array_fill(0, 4097, AttributeValue::number(0))),
            tableName: 'Music',
            topK: 2,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SearchVectorsRequest(
            indexName: 'LyricsIndex',
            searchVector: $this->searchVector(),
            tableName: str_repeat('a', 1025),
            topK: 2,
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
    private function documentedRequest(): SearchVectorsRequest
    {
        return SearchVectorsRequest::nearest(
            indexName: 'LyricsIndex',
            searchVector: ['0.12', '-0.5', '0.83'],
            tableName: 'Music',
            topK: 2,
            returnConsumedCapacity: ReturnConsumedCapacity::TOTAL,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function searchVector(): AttributeValueList
    {
        return new AttributeValueList([
            AttributeValue::number('0.12'),
            AttributeValue::number('-0.5'),
            AttributeValue::number('0.83'),
        ]);
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
