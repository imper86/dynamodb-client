<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class ExportSummary
{
    public function __construct(
        public ?string $exportArn = null,
        public ?ExportStatus $exportStatus = null,
        public ?ExportType $exportType = null,
    ) {}
}
