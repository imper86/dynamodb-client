<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\SearchResultItemList;
use Imper86\DynamoDBClient\Model\VectorCapacity;

final readonly class SearchVectorsResponse
{
    /**
     * @param SearchResultItemList $searchResults the most similar item first
     */
    public function __construct(
        public SearchResultItemList $searchResults = new SearchResultItemList(),
        public ?VectorCapacity $consumedCapacity = null,
    ) {}
}
