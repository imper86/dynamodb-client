<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class GetResourcePolicyRequest
{
    /**
     * @param non-empty-string $resourceArn the ARN of the table or stream whose policy to read
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $resourceArn,
    ) {
        Assert::stringNotEmpty($this->resourceArn);
        Assert::maxLength($this->resourceArn, 1283);
    }
}
