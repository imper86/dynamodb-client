<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\ExportSummaryList;

final readonly class ListExportsResponse
{
    /**
     * An account without exports answers with an empty `ExportSummaries` list, so it defaults to empty
     * instead of to null.
     *
     * @param null|string $nextToken where the next page starts; absent on the last page
     */
    public function __construct(
        public ExportSummaryList $exportSummaries = new ExportSummaryList(),
        public ?string $nextToken = null,
    ) {}
}
