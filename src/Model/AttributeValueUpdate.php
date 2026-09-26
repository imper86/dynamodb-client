<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 * A legacy update of one attribute. `Action` defaults to `PUT` on the service side; only a `DELETE` may go
 * without a `Value`, in which case it removes the whole attribute.
 */
final class AttributeValueUpdate
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly ?AttributeAction $action = null,
        public readonly ?AttributeValue $value = null,
    ) {
        if (AttributeAction::DELETE !== $this->action) {
            Assert::notNull($this->value, 'Only a DELETE can go without a Value.');
        }
    }

    /**
     * Adds a number to the attribute, or elements to a set, creating the attribute when it is absent.
     *
     * @throws InvalidArgumentException
     */
    public static function add(AttributeValue $value): self
    {
        return new self(action: AttributeAction::ADD, value: $value);
    }

    /**
     * Sets the attribute to this value, replacing whatever it held.
     *
     * @throws InvalidArgumentException
     */
    public static function put(AttributeValue $value): self
    {
        return new self(action: AttributeAction::PUT, value: $value);
    }

    /**
     * Removes the whole attribute, or with a set value, only those elements from the set.
     *
     * @throws InvalidArgumentException
     */
    public static function delete(?AttributeValue $value = null): self
    {
        return new self(action: AttributeAction::DELETE, value: $value);
    }
}
