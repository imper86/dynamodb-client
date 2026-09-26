<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

final class PutResourcePolicyResponse
{
    /**
     * @param null|string $revisionId the revision of the attached policy, compared as a string
     */
    public function __construct(
        public readonly ?string $revisionId = null,
    ) {}
}
