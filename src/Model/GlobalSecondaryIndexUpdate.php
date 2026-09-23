<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

use function array_filter;
use function count;

final readonly class GlobalSecondaryIndexUpdate
{
    /**
     * Exactly one of the three actions must be given.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        public ?CreateGlobalSecondaryIndexAction $create = null,
        public ?DeleteGlobalSecondaryIndexAction $delete = null,
        public ?UpdateGlobalSecondaryIndexAction $update = null,
    ) {
        Assert::same(
            count(array_filter(
                [$this->create, $this->delete, $this->update],
                static fn(?object $action): bool => null !== $action,
            )),
            1,
            'A GlobalSecondaryIndexUpdate needs exactly one of Create, Delete or Update.',
        );
    }

    /**
     * @param non-empty-string $indexName
     * @throws InvalidArgumentException
     */
    public static function create(
        string $indexName,
        KeySchemaElementList $keySchema,
        Projection $projection,
        ?OnDemandThroughput $onDemandThroughput = null,
        ?ProvisionedThroughput $provisionedThroughput = null,
        ?WarmThroughput $warmThroughput = null,
    ): self {
        return new self(create: new CreateGlobalSecondaryIndexAction(
            indexName: $indexName,
            keySchema: $keySchema,
            projection: $projection,
            onDemandThroughput: $onDemandThroughput,
            provisionedThroughput: $provisionedThroughput,
            warmThroughput: $warmThroughput,
        ));
    }

    /**
     * @param non-empty-string $indexName
     * @throws InvalidArgumentException
     */
    public static function delete(string $indexName): self
    {
        return new self(delete: new DeleteGlobalSecondaryIndexAction($indexName));
    }

    /**
     * @param non-empty-string $indexName
     * @throws InvalidArgumentException
     */
    public static function update(
        string $indexName,
        ?OnDemandThroughput $onDemandThroughput = null,
        ?ProvisionedThroughput $provisionedThroughput = null,
        ?WarmThroughput $warmThroughput = null,
    ): self {
        return new self(update: new UpdateGlobalSecondaryIndexAction(
            indexName: $indexName,
            onDemandThroughput: $onDemandThroughput,
            provisionedThroughput: $provisionedThroughput,
            warmThroughput: $warmThroughput,
        ));
    }
}
