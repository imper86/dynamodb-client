<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class ProvisionedThroughput
{
    /**
     * @param positive-int $readCapacityUnits
     * @param positive-int $writeCapacityUnits
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly int $readCapacityUnits,
        public readonly int $writeCapacityUnits,
    ) {
        Assert::positiveInteger($this->readCapacityUnits);
        Assert::positiveInteger($this->writeCapacityUnits);
    }
}
