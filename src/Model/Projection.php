<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\NonEmptyStringList;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class Projection
{
    /**
     * @param null|NonEmptyStringList $nonKeyAttributes the attributes to project on top of the keys, for a projection type of INCLUDE
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly ?NonEmptyStringList $nonKeyAttributes = null,
        public readonly ?ProjectionType $projectionType = null,
    ) {
        Assert::nullOrMinCount($this->nonKeyAttributes, 1);
        Assert::nullOrMaxCount($this->nonKeyAttributes, 20);
    }
}
