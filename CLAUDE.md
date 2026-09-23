# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

An object-oriented PHP client for the DynamoDB JSON API. It maps the AWS API reference one-to-one onto
readonly value objects, signs requests itself, and speaks PSR-7/17/18 throughout. PHP >= 8.4.

## Commands

```bash
composer analyse       # cs:check + stan + rector:check + cda + unit — run before every commit
composer fix           # cs:fix + rector:fix — run this first, then analyse
composer unit          # phpunit
composer stan          # phpstan only
```

A single test class or case:

```bash
vendor/bin/phpunit tests/GetItemTest.php
vendor/bin/phpunit --filter testReturnsTheDeserializedItem
```

`composer analyse` also runs as a captainhook pre-commit action, so a commit fails on any violation.

CI (`.github/workflows/ci.yml`) runs `composer analyse` on the newest dependencies, and PHPUnit alone on
`--prefer-lowest` and on the newest Symfony 6.4 and 7.4 — PHPStan is only expected to pass against the
newest Symfony. The lock file is not committed, so every CI run resolves afresh. When lowering a
dependency floor, check it with `composer update --prefer-lowest` locally: nothing else exercises it.

## Architecture

`DynamoDBClient` is the only entry point. Every operation is a three-line delegation to the private
`sendRequest()`, which owns the whole request/response lifecycle: serialize the request object, POST it
to `/`, check for a 200, deserialize into the expected response class, and wrap every failure in an
exception from `src/Exception`. An operation method never touches HTTP or JSON itself — it just names
the `X-Amz-Target` and the response class.

**Transport** is a `php-http` `PluginClient` assembled by `PluginClientFactory`. Plugin order matters:
`BaseUriPlugin` (regional endpoint) → `HeaderDefaultsPlugin` (`application/x-amz-json-1.0`,
`identity` encoding) → `AuthorizationPlugin` (SigV4 via `Signer\SignatureV4`) → `UserAgentPlugin`. The
user agent is added *after* signing because it is not part of the signature.

**Serialization** is Symfony Serializer, configured once in `SerializerFactory`:

- `PascalCaseNameConverter` maps PHP `camelCase` to AWS `PascalCase` by plain `ucfirst`/`lcfirst`. A
  property whose wire name does not follow from that carries a `#[SerializedName]` attribute instead:
  `AttributeValue`'s `B`, `BOOL`, `NS`, …, and every member AWS spells with an acronym, since
  `ucfirst` turns `sseDescription` into `SseDescription` and `kmsMasterKeyId` into `KmsMasterKeyId`.
- `SKIP_NULL_VALUES` is on, so a null property is simply absent from the request body.
  `PRESERVE_EMPTY_OBJECTS` is on too, so an object whose properties are all null still goes on the wire
  as `{}` rather than `[]`.
- `TimestampNormalizer` handles every date, because AWS puts a `Timestamp` on the wire as a JSON
  number of epoch seconds with a fractional part rather than as a date string. Symfony's
  `DateTimeNormalizer` cannot emit a number before 7.1 (`CAST_KEY`), so do not swap it back while
  Symfony 6.4 is supported. Type such a member as `DateTimeImmutable`; it comes back in UTC.
- `CollectionNormalizer` handles everything implementing `ValueObject\CollectionInterface`, turning
  lists and sets into JSON arrays and maps into JSON objects (an empty map stays `{}`, not `[]`), and
  delegating items back to the serializer. Constructor validation failures surface as
  `NotNormalizableValueException`, which `sendRequest()` then wraps.
- `ScalarRejectingDenormalizer` sits just before `PropertyNormalizer` and rejects a scalar where an
  object is expected. `PropertyNormalizer` would cast `"x"` to `["x"]` and build the object from its
  defaults, so a malformed body would arrive as an empty value instead of an error. `null` still passes
  through and reads as an absent member.

Because deserialization goes through the constructor, **a required constructor parameter that the
service omits turns the whole call into a `ResponseDeserializationException`**. This drives the
nullability rules below.

**Collections** live in `src/ValueObject`: `AbstractList` / `AbstractMap` / `AbstractSet` for scalars,
`AbstractObjectList` / `AbstractObjectMap` for object items (these declare `itemType()`, which is how
the normalizer knows what to build). They are immutable, validate in the constructor, and expose
`toArray()`, `get()`, `count()`, `isEmpty()`.

**Exceptions** all implement `Exception\ExceptionInterface`, so a caller can catch that one type.

## Adding an API operation

The existing operations — `batchExecuteStatement`, `batchGetItem`, `batchWriteItem`, `createBackup`,
`createTable`, `deleteBackup`, `deleteItem`, `deleteResourcePolicy`, `deleteTable`, `describeBackup`,
`describeContinuousBackups`, `describeContributorInsights`, `describeEndpoints`, `describeExport`,
`describeImport`, `describeKinesisStreamingDestination`, `describeLimits`, `describeTable`,
`describeTableReplicaAutoScaling`, `describeTimeToLive`, `disableKinesisStreamingDestination`,
`enableKinesisStreamingDestination`, `executeStatement`, `executeTransaction`, `exportTableToPointInTime`, `getItem`,
`getResourcePolicy`, `importTable`, `listBackups`, `listContributorInsights`, `listExports`, `listImports`, `listTables`, `listTagsOfResource`, `putItem`, `putResourcePolicy`, `query`, `restoreTableFromBackup`, `restoreTableToPointInTime`, `scan`, `searchVectors` — are the templates.
Read one end to end before starting another.
`createTable` is the one with a large type tree; most of its models (`KeySchemaElement`, `Projection`,
`ProvisionedThroughput`, `ReplicaDescription`, `TableDescription`, …) are the ones `describeTable`
and `updateTable` will reuse.

**1. Read the AWS reference.** The docs are fetchable as Markdown:

```bash
curl -sL https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_<Operation>.md
```

Follow the links to each nested type (`API_KeysAndAttributes.md`, `API_BatchStatementResponse.md`, …).
The **Examples** section gives realistic request/response payloads to use as test fixtures.

**2. Check what is actually required.** The docs mark `Required:` on request parameters only; the
Response Elements section never does. For the real constraints — required members, min/max sizes —
read the service model:

```bash
curl -s https://raw.githubusercontent.com/boto/botocore/develop/botocore/data/dynamodb/2012-08-10/service-2.json
```

Its output shapes mark nothing as required, which is conservative rather than informative, so decide
response nullability on documented behaviour instead (see step 5).

**3. Models** go in `src/Model`, named exactly after the AWS type (`KeysAndAttributes`,
`BatchStatementError`). Enums are backed enums over the documented valid values
(`ReturnValuesOnConditionCheckFailure`). Reuse what exists — `AttributeValue`, `AttributeValueMap`,
`ConsumedCapacity`, `Capacity` already cover most nested shapes. Give the model named constructors
where the rules below ask for them.

**4. Collections** are `final readonly` classes extending `AbstractObjectList` / `AbstractObjectMap`,
named `<ItemType>List` / `<ItemType>Map`. **Never repeat `Object` in a concrete collection's name** —
`BatchStatementRequestList`, not `BatchStatementRequestObjectList`. Only the abstract bases carry it.
Where a literal reading would give a double suffix (`Responses` in BatchGetItem is a map of lists),
name the inner list for what it holds — `ItemList`, then `ItemListMap`.

**5. Messages** go in `src/Message` as `<Operation>Request` / `<Operation>Response`, `final readonly`
with promoted constructor properties. Nullability:

| | |
|---|---|
| Request, required parameter | non-nullable, first, no default |
| Request, optional parameter | nullable, `= null`, alphabetical after the required ones |
| Response, scalar or object that may legitimately be absent | nullable, `= null` |
| Response, collection the service always sends | non-nullable, `= new <Collection>()` |
| Response, object the service always sends | still nullable, `= null` |

The empty-collection default is the important one: it gives callers a non-null payload without turning
an unexpectedly absent element into an exception that also discards the `ConsumedCapacity` that *did*
arrive. Use it for a collection AWS always returns (`Responses`, `UnprocessedKeys`). Use `null` where
absence carries meaning — `GetItemResponse::$item` is null precisely because that is how DynamoDB
reports "no matching item".

An object has no empty equivalent to default to, so it stays nullable even when the service always
sends it — `CreateBackupResponse::$backupDetails` is the whole payload, and requiring it would answer
an unexpectedly empty body with a deserialization failure instead of a response to inspect. Models
that only ever arrive in a response (`ConsumedCapacity`, `BackupDetails`) make every property
nullable with a `= null` default, in alphabetical order, and assert nothing: a constraint on a value
the service chose can only turn its answer into an exception.

Response properties are ordered payload first, `ConsumedCapacity` last, regardless of the order in the
AWS response syntax.

An operation with no request parameters (`DescribeEndpoints`, `DescribeLimits`) gets no request class — Symfony's
`PropertyNormalizer` refuses an object without properties. Its client method takes no argument and passes
`null` to `sendRequest()`, which then sends `{}`.

An operation whose request parameters are all optional (`ListBackups`, `ListTables`) does get a request
class, and its client method defaults the argument to an empty one —
`listTables(ListTablesRequest $request = new ListTablesRequest())` — so a caller who wants the defaults
can write `$client->listTables()`.

**6. Validation** lives in the constructor of the object that owns the constraint, using
`Webmozart\Assert\Assert` (`minCount`, `maxCount`, `maxLength`, `stringNotEmpty`, and the `nullOr*`
variants). Collection classes cannot carry their own size limits — `validate()` is `final` in the
abstract object collections — so a "1 to 25 statements" rule belongs in the request that holds the list.

**7. Wire it up** in `DynamoDBClient` and `DynamoDBClientInterface`, keeping methods alphabetical. The
target is `DynamoDB_20120810.<Operation>`. The interface method gets `@throws ExceptionInterface`; the
implementation needs no docblock.

**8. Test** in `tests/<Operation>Test.php` — one class per operation, `#[CoversClass(DynamoDBClient::class)]`,
with a class docblock naming which documented example the fixtures come from. Fixtures go in
`tests/fixtures/<kebab-operation>-request.json` and `-response.json`. Drive the client with
`Http\Mock\Client`, assert the serialized body with `assertJsonStringEqualsJsonFile`, and cover: the
documented round trip, each optional response element present and absent, a body that cannot
deserialize, and every validation rule. Note in the docblock if you had to correct the AWS sample —
some of them are not valid JSON.

A rule the parameter type already carries has no test: PHPStan rejects `''` for a `non-empty-string`
and `0` for a `positive-int` at the call site, so the only way to reach the `Assert` would be to hide
the value from the analyser. Leave the assertion in — it still guards callers who do not run
PHPStan — and test the rules a well-typed caller can actually break.

**9.** `composer fix && composer fix && composer analyse` — see below for why `fix` runs twice.

## Named constructors

The client takes request objects, so the request object is the API. That only stays pleasant if
building the models inside it is short. **Whenever a new model makes the caller write `new` inside
`new`, or makes them pick one of several mutually exclusive parameters, give it a named static
constructor** — `AttributeValue::string('x')` instead of `new AttributeValue(string: 'x')`,
`WriteRequest::put($item)` instead of `new WriteRequest(putRequest: new PutRequest($item))`.

- **The constructor stays the only validation point.** A factory does nothing but build its arguments
  and forward them, so every rule is enforced once and applies to deserialization too. A factory that
  reaches the constructor still needs `@throws InvalidArgumentException`, even when its own arguments
  cannot violate anything — PHPStan's `missingCheckedExceptionInThrows` counts the call.
- **A factory takes the raw ingredients, the constructor takes the built objects.** `stringSet()`
  takes `string ...$values`, `map()` takes `array<string, AttributeValue>`. Nothing is gained by an
  overload for a collection the caller already holds — `new AttributeValue(map: $map)` is short
  already. A variadic can arrive with string keys, so wrap it in `array_values()` before handing it
  to a list or set.
- **Accept what callers have, convert where the conversion is lossless.** `AttributeValue::number()`
  takes `float|int|string` because DynamoDB's `N` is a string on the wire and nobody wants to write
  the cast. It documents that a float converts with PHP's own precision.
- **Name the factory after the thing it produces**, and order the factories the way the constructor
  orders its properties (for `AttributeValue`, that is wire order: `B`, `BOOL`, `BS`, `L`, …). PHP
  allows reserved words as method names, so `list()`, `null()`, `string()` and `bool()` are fine and
  are the right names.
- **Leave response-only models alone.** `ConsumedCapacity`, `BatchStatementError` and the like are
  only ever deserialized, so a factory would be dead weight.
- **Do not put a variadic `of()` on the collections.** `new AttributeValueList([$a, $b])` is already
  flat, and the typed `list<T>` constructor parameter gives PHPStan more to check than
  `mixed ...$items` would.

## Conventions and gotchas

- **PHPStan runs at level 10** with strict rules and `missingCheckedExceptionInThrows`. Every method
  that can throw a checked exception needs an accurate `@throws`, tests included. Do not silence
  findings with baselines, `@phpstan-ignore`, casts, or widened types.
- **`phpstan-phpunit` narrows aggressively.** After `self::assertSame('x', $a?->b)` or
  `self::assertCount(2, $x ?? [])`, PHPStan knows the subject is non-null and then flags every later
  `?->` on it as `nullsafe.neverNull`. Assign to a local, `assertInstanceOf`, and use plain `->` from
  there — see `BatchGetItemTest::testReturnsTheConsumedCapacityOfEveryTable`.
- Classes are `final readonly` with promoted properties; call multi-argument constructors with named
  arguments.
- php-cs-fixer enforces `@Symfony` + `@PER-CS2.0` plus global namespace imports, so `use function`
  every global function and keep the import list sorted (`composer fix` does it).
- Rector runs with a wide set of prepared sets and a php85 target; check `rector.php` before fighting
  one of its rules.
- **`composer fix` can need two runs.** It runs php-cs-fixer *before* Rector, so a Rector rewrite — say,
  `null !== $x` into `$x instanceof \Fully\Qualified\Name` — leaves a file that `cs:check` then rejects.
  Run `composer fix` again before `composer analyse`.
- **Never `assertEquals` two objects.** Rector turns it into `assertSame`, which compares identity and
  fails for two equal value objects. Compare members instead — individual properties, or `toArray()`
  on a collection.
- **Known gap:** `Model\ConsumedCapacity` types `Table` as `?string`, but the API returns a `Capacity`
  object there, and `WriteCapacityUnits` and `VectorIndexes` are missing entirely. A response from
  `ReturnConsumedCapacity::INDEXES` will fail to deserialize for any operation.
