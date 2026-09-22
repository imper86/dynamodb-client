<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class GetResourcePolicyRequest
{
    /**
     * @param non-empty-string $resourceArn the ARN of the table or stream whose policy to read
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $resourceArn,
    ) {
        Assert::stringNotEmpty($this->resourceArn);
        Assert::maxLength($this->resourceArn, 1283);
    }
}
