<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient\Exception;

use RuntimeException;

use function implode;
use function sprintf;

final class MissingCredentialsException extends RuntimeException implements ExceptionInterface
{
    /**
     * @param list<non-empty-string> $variables
     */
    public static function fromEnvironment(array $variables): self
    {
        return new self(sprintf(
            'Unable to resolve AWS credentials, the environment variable(s) %s are missing or empty.',
            implode(', ', $variables),
        ));
    }
}
