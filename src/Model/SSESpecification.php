<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Webmozart\Assert\Assert;

final readonly class SSESpecification
{
    /**
     * @param null|non-empty-string $kmsMasterKeyId
     * @throws InvalidArgumentException
     */
    public function __construct(
        public ?bool $enabled = null,
        #[SerializedName('KMSMasterKeyId')]
        public ?string $kmsMasterKeyId = null,
        #[SerializedName('SSEType')]
        public ?SSEType $sseType = null,
    ) {
        Assert::nullOrStringNotEmpty($this->kmsMasterKeyId);
    }
}
