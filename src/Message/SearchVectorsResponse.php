<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\SearchResultItemList;
use Imper86\DynamoDBClient\Model\VectorCapacity;

final class SearchVectorsResponse
{
    /**
     * @param SearchResultItemList $searchResults the most similar item first
     */
    public function __construct(
        public readonly SearchResultItemList $searchResults = new SearchResultItemList(),
        public readonly ?VectorCapacity $consumedCapacity = null,
    ) {}
}
