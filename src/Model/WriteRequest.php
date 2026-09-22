<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class WriteRequest
{
    /**
     * Exactly one of the two requests must be given; a put and a delete need two separate write requests.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        public ?DeleteRequest $deleteRequest = null,
        public ?PutRequest $putRequest = null,
    ) {
        Assert::true(
            (!$this->deleteRequest instanceof DeleteRequest) !== (!$this->putRequest instanceof PutRequest),
            'A WriteRequest needs exactly one of DeleteRequest or PutRequest.',
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function delete(AttributeValueMap $key): self
    {
        return new self(deleteRequest: new DeleteRequest($key));
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function put(AttributeValueMap $item): self
    {
        return new self(putRequest: new PutRequest($item));
    }
}
