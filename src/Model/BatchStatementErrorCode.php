<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum BatchStatementErrorCode: string
{
    case ACCESS_DENIED = 'AccessDenied';
    case CONDITIONAL_CHECK_FAILED = 'ConditionalCheckFailed';
    case DUPLICATE_ITEM = 'DuplicateItem';
    case INTERNAL_SERVER_ERROR = 'InternalServerError';
    case ITEM_COLLECTION_SIZE_LIMIT_EXCEEDED = 'ItemCollectionSizeLimitExceeded';
    case PROVISIONED_THROUGHPUT_EXCEEDED = 'ProvisionedThroughputExceeded';
    case REQUEST_LIMIT_EXCEEDED = 'RequestLimitExceeded';
    case RESOURCE_NOT_FOUND = 'ResourceNotFound';
    case THROTTLING_ERROR = 'ThrottlingError';
    case TRANSACTION_CONFLICT = 'TransactionConflict';
    case VALIDATION_ERROR = 'ValidationError';
}
