<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

/**
 * The reference lists `NEW_IMAGES`, but the service model, which is what goes on the wire, spells it `NEW_IMAGE`.
 */
enum ExportViewType: string
{
    case NEW_AND_OLD_IMAGES = 'NEW_AND_OLD_IMAGES';
    case NEW_IMAGE = 'NEW_IMAGE';
}
