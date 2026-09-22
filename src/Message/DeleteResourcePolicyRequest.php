<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class DeleteResourcePolicyRequest
{
    /**
     * @param non-empty-string $resourceArn the ARN of the table or stream whose policy to delete
     * @param null|non-empty-string $expectedRevisionId deletes the policy only while it is at this revision
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $resourceArn,
        public ?string $expectedRevisionId = null,
    ) {
        Assert::stringNotEmpty($this->resourceArn);
        Assert::maxLength($this->resourceArn, 1283);
        Assert::nullOrStringNotEmpty($this->expectedRevisionId);
        Assert::nullOrMaxLength($this->expectedRevisionId, 255);
    }
}
