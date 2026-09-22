<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class ProvisionedThroughput
{
    /**
     * @param positive-int $readCapacityUnits
     * @param positive-int $writeCapacityUnits
     * @throws InvalidArgumentException
     */
    public function __construct(
        public int $readCapacityUnits,
        public int $writeCapacityUnits,
    ) {
        Assert::positiveInteger($this->readCapacityUnits);
        Assert::positiveInteger($this->writeCapacityUnits);
    }
}
