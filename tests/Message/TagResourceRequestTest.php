<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Message;

use Imper86\DynamoDBClient\Message\TagResourceRequest;
use Imper86\DynamoDBClient\Model\Tag;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TagResourceRequest::class)]
final class TagResourceRequestTest extends TestCase
{
    private const RESOURCE_ARN = 'arn:aws:dynamodb:eu-central-1:123456789012:table/Music';

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsATagFromEveryEntryOfTheMap(): void
    {
        $request = TagResourceRequest::tags(self::RESOURCE_ARN, ['Environment' => 'production', 'Owner' => '']);

        self::assertSame(self::RESOURCE_ARN, $request->resourceArn);
        self::assertCount(2, $request->tags);

        $owner = $request->tags->get(1);

        self::assertInstanceOf(Tag::class, $owner);
        self::assertSame('Owner', $owner->key);
        self::assertSame('', $owner->value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testKeepsANumericKeyAString(): void
    {
        $request = TagResourceRequest::tags(self::RESOURCE_ARN, ['2024' => 'budget']);

        self::assertSame('2024', $request->tags->get(0)?->key);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testValidatesThroughTheConstructors(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TagResourceRequest::tags(self::RESOURCE_ARN, ['Environment' => str_repeat('a', 257)]);
    }
}
