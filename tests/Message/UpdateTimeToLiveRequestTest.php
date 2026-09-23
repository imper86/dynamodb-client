<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Message;

use Imper86\DynamoDBClient\Message\UpdateTimeToLiveRequest;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function str_repeat;

/**
 * @internal
 */
#[CoversClass(UpdateTimeToLiveRequest::class)]
final class UpdateTimeToLiveRequestTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testEnables(): void
    {
        $request = UpdateTimeToLiveRequest::enable('Music', 'ExpiresAt');

        self::assertSame('Music', $request->tableName);
        self::assertSame('ExpiresAt', $request->timeToLiveSpecification->attributeName);
        self::assertTrue($request->timeToLiveSpecification->enabled);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testDisables(): void
    {
        $request = UpdateTimeToLiveRequest::disable('Music', 'ExpiresAt');

        self::assertSame('Music', $request->tableName);
        self::assertSame('ExpiresAt', $request->timeToLiveSpecification->attributeName);
        self::assertFalse($request->timeToLiveSpecification->enabled);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testValidatesThroughTheConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UpdateTimeToLiveRequest::enable(str_repeat('a', 1025), 'ExpiresAt');
    }
}
