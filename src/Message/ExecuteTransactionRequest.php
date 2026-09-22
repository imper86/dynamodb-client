<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\ParameterizedStatementList;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Webmozart\Assert\Assert;

final readonly class ExecuteTransactionRequest
{
    /**
     * @param null|non-empty-string $clientRequestToken makes the call idempotent: repeating a request with
     *                                                  the same token has the effect of running it once
     * @throws InvalidArgumentException
     */
    public function __construct(
        public ParameterizedStatementList $transactStatements,
        public ?string $clientRequestToken = null,
        public ?ReturnConsumedCapacity $returnConsumedCapacity = null,
    ) {
        Assert::minCount($this->transactStatements, 1);
        Assert::maxCount($this->transactStatements, 100);
        Assert::nullOrStringNotEmpty($this->clientRequestToken);
        Assert::nullOrMaxLength($this->clientRequestToken, 36);
    }
}
