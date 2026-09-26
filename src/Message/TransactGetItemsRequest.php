<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\TransactGetItemList;
use Webmozart\Assert\Assert;

final class TransactGetItemsRequest
{
    /**
     * `ReturnConsumedCapacity` is shared with the other item operations, but TransactGetItems accepts only
     * `TOTAL` and `NONE`.
     *
     * @param TransactGetItemList $transactItems the items to read, from tables but not from indexes
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly TransactGetItemList $transactItems,
        public readonly ?ReturnConsumedCapacity $returnConsumedCapacity = null,
    ) {
        Assert::minCount($this->transactItems, 1);
        Assert::maxCount($this->transactItems, 100);
        Assert::nullOrOneOf(
            $this->returnConsumedCapacity,
            [ReturnConsumedCapacity::TOTAL, ReturnConsumedCapacity::NONE],
        );
    }
}
