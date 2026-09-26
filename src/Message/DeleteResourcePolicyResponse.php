<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

final class DeleteResourcePolicyResponse
{
    /**
     * @param null|string $revisionId the revision of the deleted policy, compared as a string; empty
     *                                when the resource had no policy
     */
    public function __construct(
        public readonly ?string $revisionId = null,
    ) {}
}
