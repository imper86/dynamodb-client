<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class SSEDescription
{
    public function __construct(
        public ?DateTimeImmutable $inaccessibleEncryptionDateTime = null,
        #[SerializedName('KMSMasterKeyArn')]
        public ?string $kmsMasterKeyArn = null,
        #[SerializedName('SSEType')]
        public ?SSEType $sseType = null,
        public ?SSEStatus $status = null,
    ) {}
}
