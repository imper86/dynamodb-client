# DynamoDB Client

An object-oriented PHP client for the [Amazon DynamoDB API](https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/).

- **It maps the AWS API one to one.** Every operation takes a request object and returns a response
  object, and each class and property is named after the matching type and member in the AWS API
  reference. You can read the AWS docs and know which class to use.
- **It uses typed, immutable value objects.** Requests, responses and models are `final readonly`
  classes, and every fixed set of values is a backed enum. The code passes PHPStan at level 10, so
  your IDE and static analyser can check your calls too.
- **It is built on PSR standards and has few dependencies.** It works with any PSR-18 HTTP client and
  any PSR-17 factories. It signs requests with Signature V4 itself, so you do not need the AWS SDK.

## Installation

```bash
composer require imper86/dynamodb-client
```

You need PHP 8.4 or newer, plus a PSR-18 HTTP client and PSR-17 factories. If your project has none
yet, install one, for example:

```bash
composer require symfony/http-client nyholm/psr7
```

The client finds them through [`php-http/discovery`](https://github.com/php-http/discovery). It works
with Symfony Serializer 6.4, 7.4 and 8.x.

## Creating the client

```php
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Model\Credentials;

// Credentials come from AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY and, if set, AWS_SESSION_TOKEN.
$client = new DynamoDBClient('eu-central-1');

// Or you pass them in yourself.
$client = new DynamoDBClient(
    region: 'eu-central-1',
    credentials: new Credentials('AKIA...', 'secret', token: null),
);
```

The constructor also accepts your own PSR-18 `httpClient`, PSR-17 `requestFactory` and
`streamFactory`, and a Symfony `serializer`. Requests go to `https://dynamodb.<region>.amazonaws.com`.

Type your dependencies against `DynamoDBClientInterface`, which makes the client easy to mock in tests.

## Usage

### Attribute values

An item is an `AttributeValueMap` of `AttributeValue`s. The named constructors cover every DynamoDB
type:

```php
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueMap;

$item = new AttributeValueMap([
    'Artist'    => AttributeValue::string('No One You Know'),
    'SongTitle' => AttributeValue::string('Call Me Today'),
    'Year'      => AttributeValue::number(2015),          // int, float or numeric string
    'Genres'    => AttributeValue::stringSet('Country', 'Pop'),
    'Awards'    => AttributeValue::map([
        'Grammy' => AttributeValue::bool(false),
    ]),
]);
```

The full list is `blob()`, `bool()`, `blobSet()`, `list()`, `map()`, `number()`, `numberSet()`,
`null()`, `string()` and `stringSet()`. To read a value back, use the matching property, such as
`$value->string`, `$value->number` or `$value->map`.

### Creating a table

```php
use Imper86\DynamoDBClient\Message\CreateTableRequest;
use Imper86\DynamoDBClient\Model\AttributeDefinition;
use Imper86\DynamoDBClient\Model\AttributeDefinitionList;
use Imper86\DynamoDBClient\Model\BillingMode;
use Imper86\DynamoDBClient\Model\KeySchemaElement;
use Imper86\DynamoDBClient\Model\KeySchemaElementList;
use Imper86\DynamoDBClient\Model\KeyType;
use Imper86\DynamoDBClient\Model\ScalarAttributeType;

$client->createTable(new CreateTableRequest(
    tableName: 'Music',
    attributeDefinitions: new AttributeDefinitionList([
        new AttributeDefinition('Artist', ScalarAttributeType::STRING),
        new AttributeDefinition('SongTitle', ScalarAttributeType::STRING),
    ]),
    billingMode: BillingMode::PAY_PER_REQUEST,
    keySchema: new KeySchemaElementList([
        new KeySchemaElement('Artist', KeyType::HASH),
        new KeySchemaElement('SongTitle', KeyType::RANGE),
    ]),
));
```

### Writing and reading items

```php
use Imper86\DynamoDBClient\Message\GetItemRequest;
use Imper86\DynamoDBClient\Message\PutItemRequest;

$client->putItem(new PutItemRequest(
    item: $item,
    tableName: 'Music',
    conditionExpression: 'attribute_not_exists(SongTitle)',
));

$response = $client->getItem(new GetItemRequest(
    key: new AttributeValueMap([
        'Artist'    => AttributeValue::string('No One You Know'),
        'SongTitle' => AttributeValue::string('Call Me Today'),
    ]),
    tableName: 'Music',
    consistentRead: true,
));

if (null === $response->item) {
    // DynamoDB reports "no such item" as an absent Item.
} else {
    echo $response->item->get('Year')?->number; // "2015"
}
```

### Querying with pagination

```php
use Imper86\DynamoDBClient\Message\QueryRequest;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;

$startKey = null;

do {
    $page = $client->query(new QueryRequest(
        tableName: 'Music',
        exclusiveStartKey: $startKey,
        expressionAttributeValues: new AttributeValueMap([
            ':artist' => AttributeValue::string('No One You Know'),
        ]),
        keyConditionExpression: 'Artist = :artist',
        returnConsumedCapacity: ReturnConsumedCapacity::TOTAL,
    ));

    foreach ($page->items as $item) {
        echo $item->get('SongTitle')?->string, PHP_EOL;
    }

    $startKey = $page->lastEvaluatedKey;
} while (null !== $startKey);
```

### Batch writes

```php
use Imper86\DynamoDBClient\Message\BatchWriteItemRequest;
use Imper86\DynamoDBClient\Model\WriteRequest;
use Imper86\DynamoDBClient\Model\WriteRequestList;
use Imper86\DynamoDBClient\Model\WriteRequestListMap;

$response = $client->batchWriteItem(new BatchWriteItemRequest(
    requestItems: new WriteRequestListMap([
        'Music' => new WriteRequestList([
            WriteRequest::put($item),
            WriteRequest::delete($key),
        ]),
    ]),
));

// Retry anything DynamoDB did not process.
$response->unprocessedItems;
```

### Named constructors on requests

Some requests have several mutually exclusive modes. These requests have a factory for each mode,
so you do not have to build the nested models yourself:

```php
UpdateTimeToLiveRequest::enable('Music', 'ExpiresAt');
UpdateContinuousBackupsRequest::enable('Music', recoveryPeriodInDays: 7);
RestoreTableToPointInTimeRequest::latest(...);   // or ::at(...)
ExportTableToPointInTimeRequest::full(...);      // or ::incremental(...)
ImportTableRequest::csv(...);                    // or ::dynamoDbJson(...), ::ion(...)
TagResourceRequest::tags($arn, ['env' => 'prod']);
```

If an operation takes only optional parameters, you can call it without a request object:
`$client->listTables()`, `$client->listBackups()`, `$client->describeLimits()`.

## Error handling

Every exception the client throws implements `Imper86\DynamoDBClient\Exception\ExceptionInterface`:

| Exception | When |
|---|---|
| `BadResponseException` | DynamoDB answered with a status other than 200. Its `$response` property holds the PSR-7 response, whose body contains the AWS error `__type` and `message`. |
| `HttpClientException` | The PSR-18 client failed, for example with a network error. |
| `RequestSerializationException` | The request object could not be serialized. |
| `ResponseDeserializationException` | The response body could not be turned into the response class. |
| `InvalidArgumentException` | A value broke a constraint of the API. |
| `MissingCredentialsException` | You passed no credentials and the environment does not provide any. |

```php
use Imper86\DynamoDBClient\Exception\BadResponseException;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;

try {
    $client->deleteTable(new DeleteTableRequest('Music'));
} catch (BadResponseException $e) {
    $error = json_decode((string) $e->response->getBody(), true);
} catch (ExceptionInterface $e) {
    // anything else from the client
}
```

Models and requests check the API's constraints, such as sizes, counts and mutually exclusive members,
in their constructors. When you build one yourself outside a client call, it throws
`Webmozart\Assert\InvalidArgumentException`.

## Supported operations

52 operations of the DynamoDB API (`DynamoDB_20120810`) are covered:

`batchExecuteStatement`, `batchGetItem`, `batchWriteItem`, `createBackup`, `createTable`,
`deleteBackup`, `deleteItem`, `deleteResourcePolicy`, `deleteTable`, `describeBackup`,
`describeContinuousBackups`, `describeContributorInsights`, `describeEndpoints`, `describeExport`,
`describeImport`, `describeKinesisStreamingDestination`, `describeLimits`, `describeTable`,
`describeTableReplicaAutoScaling`, `describeTimeToLive`, `disableKinesisStreamingDestination`,
`enableKinesisStreamingDestination`, `executeStatement`, `executeTransaction`,
`exportTableToPointInTime`, `getItem`, `getResourcePolicy`, `importTable`, `listBackups`,
`listContributorInsights`, `listExports`, `listImports`, `listTables`, `listTagsOfResource`, `putItem`,
`putResourcePolicy`, `query`, `restoreTableFromBackup`, `restoreTableToPointInTime`, `scan`,
`searchVectors`, `tagResource`, `transactGetItems`, `transactWriteItems`, `untagResource`,
`updateContinuousBackups`, `updateContributorInsights`, `updateItem`,
`updateKinesisStreamingDestination`, `updateTable`, `updateTableReplicaAutoScaling`,
`updateTimeToLive`.

The operations of the legacy Global Tables version 2017.11.29 are not covered: `CreateGlobalTable`,
`DescribeGlobalTable`, `DescribeGlobalTableSettings`, `ListGlobalTables`, `UpdateGlobalTable` and
`UpdateGlobalTableSettings`. For current global tables, add replicas with `updateTable` instead.

## Known limitations

- The endpoint is always the regional AWS endpoint. A custom endpoint, such as DynamoDB Local, is not
  supported yet.
- The client does not retry. Retries on throttling and `UnprocessedItems` / `UnprocessedKeys` are up
  to you.
- `ReturnConsumedCapacity::INDEXES` is not supported yet: `ConsumedCapacity` cannot read the per-table
  breakdown it returns, so the response fails to deserialize. Use `TOTAL`.

## Development

```bash
composer fix       # php-cs-fixer + Rector
composer analyse   # code style, PHPStan (level 10), Rector, dependency analysis, PHPUnit
composer unit      # PHPUnit only
```

## License

MIT. See [LICENSE](LICENSE).
