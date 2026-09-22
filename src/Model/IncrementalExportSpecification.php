<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final readonly class IncrementalExportSpecification
{
    /**
     * @param null|DateTimeImmutable $exportFromTime the start of the exported period, inclusive
     * @param null|DateTimeImmutable $exportToTime the end of the exported period, exclusive
     * @param null|ExportViewType $exportViewType which images of a changed item the export holds
     */
    public function __construct(
        public ?DateTimeImmutable $exportFromTime = null,
        public ?DateTimeImmutable $exportToTime = null,
        public ?ExportViewType $exportViewType = null,
    ) {}
}
