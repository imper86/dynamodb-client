<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\ReturnItemCollectionMetrics;
use Imper86\DynamoDBClient\Model\WriteRequestListMap;
use Webmozart\Assert\Assert;

use function count;

final readonly class BatchWriteItemRequest
{
    /**
     * The 25-request limit applies to the batch as a whole, not to each table.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        public WriteRequestListMap $requestItems,
        public ?ReturnConsumedCapacity $returnConsumedCapacity = null,
        public ?ReturnItemCollectionMetrics $returnItemCollectionMetrics = null,
    ) {
        Assert::minCount($this->requestItems, 1);
        Assert::maxCount($this->requestItems, 25);

        $writeRequests = 0;

        foreach ($this->requestItems as $requestItem) {
            Assert::minCount($requestItem, 1);
            $writeRequests += count($requestItem);
        }

        Assert::lessThanEq($writeRequests, 25, 'Expected at most 25 write requests in the batch. Got: %s');
    }
}
