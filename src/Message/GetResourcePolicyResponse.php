<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

final class GetResourcePolicyResponse
{
    /**
     * @param null|string $policy the policy document attached to the resource, as a JSON string
     * @param null|string $revisionId the revision of the policy, compared as a string
     */
    public function __construct(
        public readonly ?string $policy = null,
        public readonly ?string $revisionId = null,
    ) {}
}
