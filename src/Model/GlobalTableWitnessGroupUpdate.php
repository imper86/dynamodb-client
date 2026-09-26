<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class GlobalTableWitnessGroupUpdate
{
    /**
     * Exactly one of the two actions must be given.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly ?CreateGlobalTableWitnessGroupMemberAction $create = null,
        public readonly ?DeleteGlobalTableWitnessGroupMemberAction $delete = null,
    ) {
        Assert::true(
            (!$this->create instanceof CreateGlobalTableWitnessGroupMemberAction)
                !== (!$this->delete instanceof DeleteGlobalTableWitnessGroupMemberAction),
            'A GlobalTableWitnessGroupUpdate needs exactly one of Create or Delete.',
        );
    }

    /**
     * @param non-empty-string $regionName
     * @throws InvalidArgumentException
     */
    public static function create(string $regionName): self
    {
        return new self(create: new CreateGlobalTableWitnessGroupMemberAction($regionName));
    }

    /**
     * @param non-empty-string $regionName
     * @throws InvalidArgumentException
     */
    public static function delete(string $regionName): self
    {
        return new self(delete: new DeleteGlobalTableWitnessGroupMemberAction($regionName));
    }
}
