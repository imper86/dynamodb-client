<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\ReturnItemCollectionMetrics;
use Imper86\DynamoDBClient\Model\TransactWriteItemList;
use Webmozart\Assert\Assert;

final class TransactWriteItemsRequest
{
    /**
     * @param TransactWriteItemList $transactItems the actions to run atomically; no two may target the same item
     * @param null|non-empty-string $clientRequestToken makes the call idempotent for ten minutes: repeating a
     *                                                  request with the same token has the effect of running it once
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly TransactWriteItemList $transactItems,
        public readonly ?string $clientRequestToken = null,
        public readonly ?ReturnConsumedCapacity $returnConsumedCapacity = null,
        public readonly ?ReturnItemCollectionMetrics $returnItemCollectionMetrics = null,
    ) {
        Assert::minCount($this->transactItems, 1);
        Assert::maxCount($this->transactItems, 100);
        Assert::nullOrStringNotEmpty($this->clientRequestToken);
        Assert::nullOrMaxLength($this->clientRequestToken, 36);
    }
}
