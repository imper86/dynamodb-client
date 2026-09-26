<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ConsumedCapacity;
use Imper86\DynamoDBClient\Model\ItemList;

final class ExecuteStatementResponse
{
    /**
     * @param ItemList $items the items a read returned; empty for a write
     * @param null|AttributeValueMap $lastEvaluatedKey the key the read stopped at, or null on the last page
     * @param null|string $nextToken the token to pass to the next request, or null when the result is complete
     */
    public function __construct(
        public readonly ItemList $items = new ItemList(),
        public readonly ?AttributeValueMap $lastEvaluatedKey = null,
        public readonly ?string $nextToken = null,
        public readonly ?ConsumedCapacity $consumedCapacity = null,
    ) {}
}
