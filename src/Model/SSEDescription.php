<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;
use Symfony\Component\Serializer\Attribute\SerializedName;

final class SSEDescription
{
    public function __construct(
        public readonly ?DateTimeImmutable $inaccessibleEncryptionDateTime = null,
        #[SerializedName('KMSMasterKeyArn')]
        public readonly ?string $kmsMasterKeyArn = null,
        #[SerializedName('SSEType')]
        public readonly ?SSEType $sseType = null,
        public readonly ?SSEStatus $status = null,
    ) {}
}
