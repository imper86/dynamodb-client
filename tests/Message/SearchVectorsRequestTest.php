<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Message;

use Imper86\DynamoDBClient\Message\SearchVectorsRequest;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SearchVectorsRequest::class)]
final class SearchVectorsRequestTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testConvertsEveryDimensionToANumber(): void
    {
        $request = SearchVectorsRequest::nearest(
            indexName: 'LyricsIndex',
            searchVector: [0.5, -1, '0.83'],
            tableName: 'Music',
            topK: 2,
        );

        self::assertCount(3, $request->searchVector);
        self::assertSame('0.5', $request->searchVector->get(0)?->number);
        self::assertSame('-1', $request->searchVector->get(1)?->number);
        self::assertSame('0.83', $request->searchVector->get(2)?->number);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testDropsTheKeysOfTheVector(): void
    {
        $request = SearchVectorsRequest::nearest(
            indexName: 'LyricsIndex',
            searchVector: ['x' => 0.5, 'y' => 0.25],
            tableName: 'Music',
            topK: 2,
        );

        self::assertSame('0.25', $request->searchVector->get(1)?->number);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testForwardsTheOptionalParameters(): void
    {
        $request = SearchVectorsRequest::nearest(
            indexName: 'LyricsIndex',
            searchVector: [0.5],
            tableName: 'Music',
            topK: 2,
            returnConsumedCapacity: ReturnConsumedCapacity::TOTAL,
            searchConditionExpression: 'Artist = :a',
        );

        self::assertSame(ReturnConsumedCapacity::TOTAL, $request->returnConsumedCapacity);
        self::assertSame('Artist = :a', $request->searchConditionExpression);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testValidatesThroughTheConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SearchVectorsRequest::nearest(indexName: 'LyricsIndex', searchVector: [], tableName: 'Music', topK: 2);
    }
}
