<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class DeleteGlobalSecondaryIndexAction
{
    /**
     * @param non-empty-string $indexName the global secondary index to remove
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $indexName,
    ) {
        Assert::stringNotEmpty($this->indexName);
        Assert::minLength($this->indexName, 3);
        Assert::maxLength($this->indexName, 255);
        Assert::regex($this->indexName, '/^[a-zA-Z0-9_.\-]+$/');
    }
}
