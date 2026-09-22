<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

/**
 * The last failure a Contributor Insights change ran into, as the service reports it. Despite the AWS
 * name, this is a plain value object and nothing throws it.
 */
final readonly class FailureException
{
    /**
     * @param null|string $exceptionName such as `LimitExceededException` or `AccessDeniedException`
     */
    public function __construct(
        public ?string $exceptionDescription = null,
        public ?string $exceptionName = null,
    ) {}
}
