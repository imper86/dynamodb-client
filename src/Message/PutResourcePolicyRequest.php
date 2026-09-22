<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class PutResourcePolicyRequest
{
    /**
     * @param non-empty-string $policy the resource-based policy document, as JSON
     * @param non-empty-string $resourceArn the ARN of the table or stream to attach the policy to
     * @param null|bool $confirmRemoveSelfResourceAccess true to confirm a policy that takes away the caller's own
     *                                                   permission to change it later
     * @param null|non-empty-string $expectedRevisionId replaces the policy only while it is at this revision;
     *                                                  `NO_POLICY` attaches it only while there is none
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $policy,
        public string $resourceArn,
        public ?bool $confirmRemoveSelfResourceAccess = null,
        public ?string $expectedRevisionId = null,
    ) {
        Assert::stringNotEmpty($this->policy);
        Assert::stringNotEmpty($this->resourceArn);
        Assert::maxLength($this->resourceArn, 1283);
        Assert::nullOrStringNotEmpty($this->expectedRevisionId);
        Assert::nullOrMaxLength($this->expectedRevisionId, 255);
    }
}
