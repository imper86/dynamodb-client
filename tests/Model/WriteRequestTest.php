<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\WriteRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(WriteRequest::class)]
final class WriteRequestTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAPut(): void
    {
        $item = new AttributeValueMap(['Name' => AttributeValue::string('Amazon ElastiCache')]);

        $writeRequest = WriteRequest::put($item);

        self::assertNull($writeRequest->deleteRequest);
        self::assertSame($item, $writeRequest->putRequest?->item);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsADelete(): void
    {
        $key = new AttributeValueMap(['Name' => AttributeValue::string('Amazon RDS')]);

        $writeRequest = WriteRequest::delete($key);

        self::assertNull($writeRequest->putRequest);
        self::assertSame($key, $writeRequest->deleteRequest?->key);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAWriteRequestWithoutAnyRequest(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/A WriteRequest needs exactly one of DeleteRequest or PutRequest\./');

        new WriteRequest();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAWriteRequestWithBothRequests(): void
    {
        $item = new AttributeValueMap(['Name' => AttributeValue::string('Amazon ElastiCache')]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/A WriteRequest needs exactly one of DeleteRequest or PutRequest\./');

        new WriteRequest(
            deleteRequest: WriteRequest::delete($item)->deleteRequest,
            putRequest: WriteRequest::put($item)->putRequest,
        );
    }
}
