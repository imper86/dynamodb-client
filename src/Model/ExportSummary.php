<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class ExportSummary
{
    public function __construct(
        public readonly ?string $exportArn = null,
        public readonly ?ExportStatus $exportStatus = null,
        public readonly ?ExportType $exportType = null,
    ) {}
}
