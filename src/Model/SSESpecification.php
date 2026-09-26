<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Webmozart\Assert\Assert;

final class SSESpecification
{
    /**
     * @param null|non-empty-string $kmsMasterKeyId
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly ?bool $enabled = null,
        #[SerializedName('KMSMasterKeyId')]
        public readonly ?string $kmsMasterKeyId = null,
        #[SerializedName('SSEType')]
        public readonly ?SSEType $sseType = null,
    ) {
        Assert::nullOrStringNotEmpty($this->kmsMasterKeyId);
    }
}
