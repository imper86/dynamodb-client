<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\ImportSummaryList;

final readonly class ListImportsResponse
{
    /**
     * An account without imports answers with an empty `ImportSummaryList`, so it defaults to empty
     * instead of to null.
     *
     * @param null|string $nextToken where the next page starts; absent on the last page
     */
    public function __construct(
        public ImportSummaryList $importSummaryList = new ImportSummaryList(),
        public ?string $nextToken = null,
    ) {}
}
