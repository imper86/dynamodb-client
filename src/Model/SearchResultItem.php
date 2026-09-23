<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class SearchResultItem
{
    /**
     * @param null|AttributeValueMap $item the attributes projected into the vector index
     * @param null|float $score lower is closer for `COSINE` and `EUCLIDEAN`, higher is closer for `DOT_PRODUCT`
     */
    public function __construct(
        public ?AttributeValueMap $item = null,
        public ?float $score = null,
    ) {}
}
