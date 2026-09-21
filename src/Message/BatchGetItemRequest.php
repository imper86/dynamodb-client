<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\KeysAndAttributesMap;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Webmozart\Assert\Assert;

final readonly class BatchGetItemRequest
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        public KeysAndAttributesMap $requestItems,
        public ?ReturnConsumedCapacity $returnConsumedCapacity = null,
    ) {
        Assert::minCount($this->requestItems, 1);
        Assert::maxCount($this->requestItems, 100);
    }
}
