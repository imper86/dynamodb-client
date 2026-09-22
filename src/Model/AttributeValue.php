<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\ValueObject\BlobSet;
use Imper86\DynamoDBClient\ValueObject\NumberSet;
use Imper86\DynamoDBClient\ValueObject\StringSet;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Webmozart\Assert\Assert;

use function array_map;
use function array_values;

final readonly class AttributeValue
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        #[SerializedName('B')]
        public ?string $blob = null,
        #[SerializedName('BOOL')]
        public ?bool $bool = null,
        #[SerializedName('BS')]
        public ?BlobSet $blobSet = null,
        #[SerializedName('L')]
        public ?AttributeValueList $list = null,
        #[SerializedName('M')]
        public ?AttributeValueMap $map = null,
        #[SerializedName('N')]
        public ?string $number = null,
        #[SerializedName('NS')]
        public ?NumberSet $numberSet = null,
        #[SerializedName('NULL')]
        public ?bool $null = null,
        #[SerializedName('S')]
        public ?string $string = null,
        #[SerializedName('SS')]
        public ?StringSet $stringSet = null,
    ) {
        Assert::nullOrNumeric($this->number);
    }

    /**
     * @param string $value the binary payload, base64 encoded the way DynamoDB expects it on the wire
     * @throws InvalidArgumentException
     */
    public static function blob(string $value): self
    {
        return new self(blob: $value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function bool(bool $value): self
    {
        return new self(bool: $value);
    }

    /**
     * @param string ...$values the binary payloads, base64 encoded the way DynamoDB expects them on the wire
     * @throws InvalidArgumentException
     */
    public static function blobSet(string ...$values): self
    {
        return new self(blobSet: new BlobSet(array_values($values)));
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function list(self ...$values): self
    {
        return new self(list: new AttributeValueList(array_values($values)));
    }

    /**
     * @param array<string, self> $values
     * @throws InvalidArgumentException
     */
    public static function map(array $values): self
    {
        return new self(map: new AttributeValueMap($values));
    }

    /**
     * A float converts with PHP's own precision, so pass a string where the exact digits matter.
     *
     * @throws InvalidArgumentException
     */
    public static function number(float|int|string $value): self
    {
        return new self(number: self::toNumber($value));
    }

    /**
     * A float converts with PHP's own precision, so pass a string where the exact digits matter.
     *
     * @throws InvalidArgumentException
     */
    public static function numberSet(float|int|string ...$values): self
    {
        return new self(numberSet: new NumberSet(array_map(self::toNumber(...), array_values($values))));
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function null(): self
    {
        return new self(null: true);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function string(string $value): self
    {
        return new self(string: $value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function stringSet(string ...$values): self
    {
        return new self(stringSet: new StringSet(array_values($values)));
    }

    /**
     * @return numeric-string
     * @throws InvalidArgumentException
     */
    private static function toNumber(float|int|string $value): string
    {
        $number = (string) $value;

        Assert::numeric($number);

        return $number;
    }
}
