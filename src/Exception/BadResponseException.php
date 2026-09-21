<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Exception;

use Exception;
use Psr\Http\Message\ResponseInterface;

use function sprintf;

final class BadResponseException extends Exception implements ExceptionInterface
{
    public function __construct(public readonly ResponseInterface $response)
    {
        parent::__construct(
            sprintf(
                'Bad response (status %d): %s',
                $this->response->getStatusCode(),
                $this->response->getReasonPhrase(),
            ),
        );
    }
}
