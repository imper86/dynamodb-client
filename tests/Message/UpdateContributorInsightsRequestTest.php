<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Message;

use Imper86\DynamoDBClient\Message\UpdateContributorInsightsRequest;
use Imper86\DynamoDBClient\Model\ContributorInsightsAction;
use Imper86\DynamoDBClient\Model\ContributorInsightsMode;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UpdateContributorInsightsRequest::class)]
final class UpdateContributorInsightsRequestTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testEnables(): void
    {
        $request = UpdateContributorInsightsRequest::enable(
            'Music',
            ContributorInsightsMode::ACCESSED_AND_THROTTLED_KEYS,
        );

        self::assertSame(ContributorInsightsAction::ENABLE, $request->contributorInsightsAction);
        self::assertSame('Music', $request->tableName);
        self::assertSame(ContributorInsightsMode::ACCESSED_AND_THROTTLED_KEYS, $request->contributorInsightsMode);
        self::assertNull($request->indexName);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testDisablesAnIndex(): void
    {
        $request = UpdateContributorInsightsRequest::disable('Music', 'AlbumTitleIndex');

        self::assertSame(ContributorInsightsAction::DISABLE, $request->contributorInsightsAction);
        self::assertSame('Music', $request->tableName);
        self::assertSame('AlbumTitleIndex', $request->indexName);
        self::assertNull($request->contributorInsightsMode);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testValidatesThroughTheConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UpdateContributorInsightsRequest::enable('Music', indexName: 'ix');
    }
}
