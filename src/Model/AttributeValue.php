<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\ValueObject\BlobSet;
use Imper86\DynamoDBClient\ValueObject\NumberSet;
use Imper86\DynamoDBClient\ValueObject\StringSet;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Webmozart\Assert\Assert;

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
}
