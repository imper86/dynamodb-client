<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\KeysAndAttributesMap;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Webmozart\Assert\Assert;

final class BatchGetItemRequest
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly KeysAndAttributesMap $requestItems,
        public readonly ?ReturnConsumedCapacity $returnConsumedCapacity = null,
    ) {
        Assert::minCount($this->requestItems, 1);
        Assert::maxCount($this->requestItems, 100);
    }
}
