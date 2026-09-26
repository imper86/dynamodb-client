<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\UpdateTableReplicaAutoScalingRequest;
use Imper86\DynamoDBClient\Model\AutoScalingPolicyUpdate;
use Imper86\DynamoDBClient\Model\AutoScalingSettingsDescription;
use Imper86\DynamoDBClient\Model\AutoScalingSettingsUpdate;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndexAutoScalingUpdate;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndexAutoScalingUpdateList;
use Imper86\DynamoDBClient\Model\IndexStatus;
use Imper86\DynamoDBClient\Model\ReplicaAutoScalingDescription;
use Imper86\DynamoDBClient\Model\ReplicaAutoScalingUpdate;
use Imper86\DynamoDBClient\Model\ReplicaAutoScalingUpdateList;
use Imper86\DynamoDBClient\Model\ReplicaGlobalSecondaryIndexAutoScalingDescription;
use Imper86\DynamoDBClient\Model\ReplicaGlobalSecondaryIndexAutoScalingUpdate;
use Imper86\DynamoDBClient\Model\ReplicaGlobalSecondaryIndexAutoScalingUpdateList;
use Imper86\DynamoDBClient\Model\ReplicaStatus;
use Imper86\DynamoDBClient\Model\TableAutoScalingDescription;
use Imper86\DynamoDBClient\Model\TableStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The UpdateTableReplicaAutoScaling reference has no Examples section, so the fixtures are built from its
 * request and response syntax, retuning the auto scaling of a `Music` global table: its write capacity,
 * the read capacity of its replica in `us-east-1`, and both for the `AlbumTitleIndex` index.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_UpdateTableReplicaAutoScaling.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class UpdateTableReplicaAutoScalingTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/update-table-replica-auto-scaling-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/update-table-replica-auto-scaling-response.json';

    private const ROLE_ARN = 'arn:aws:iam::123456789012:role/aws-service-role/'
        . 'dynamodb.application-autoscaling.amazonaws.com/AWSServiceRoleForApplicationAutoScaling_DynamoDBTable';

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSendsTheRequestTheWayTheApiReferenceDocumentsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $this->createClient($httpClient)->updateTableReplicaAutoScaling($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.UpdateTableReplicaAutoScaling', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSendsOnlyTheTableNameWhenNothingElseIsGiven(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $this->createClient($httpClient)->updateTableReplicaAutoScaling(
            new UpdateTableReplicaAutoScalingRequest('Music'),
        );

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertJsonStringEqualsJsonString('{"TableName":"Music"}', $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheUpdatedAutoScalingSettings(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->updateTableReplicaAutoScaling($this->documentedRequest());

        $table = $response->tableAutoScalingDescription;

        self::assertInstanceOf(TableAutoScalingDescription::class, $table);
        self::assertSame('Music', $table->tableName);
        self::assertSame(TableStatus::ACTIVE, $table->tableStatus);

        $replica = $table->replicas?->get(0);

        self::assertInstanceOf(ReplicaAutoScalingDescription::class, $replica);
        self::assertSame('us-east-1', $replica->regionName);
        self::assertSame(ReplicaStatus::UPDATING, $replica->replicaStatus);

        $read = $replica->replicaProvisionedReadCapacityAutoScalingSettings;

        self::assertInstanceOf(AutoScalingSettingsDescription::class, $read);
        self::assertSame(self::ROLE_ARN, $read->autoScalingRoleArn);
        self::assertSame(200, $read->maximumUnits);
        self::assertSame(
            60.5,
            $read->scalingPolicies?->get(0)?->targetTrackingScalingPolicyConfiguration?->targetValue,
        );
        self::assertSame(80, $replica->replicaProvisionedWriteCapacityAutoScalingSettings?->maximumUnits);

        $index = $replica->globalSecondaryIndexes?->get(0);

        self::assertInstanceOf(ReplicaGlobalSecondaryIndexAutoScalingDescription::class, $index);
        self::assertSame('AlbumTitleIndex', $index->indexName);
        self::assertSame(IndexStatus::UPDATING, $index->indexStatus);
        self::assertSame(60, $index->provisionedReadCapacityAutoScalingSettings?->maximumUnits);
        self::assertTrue($index->provisionedWriteCapacityAutoScalingSettings?->autoScalingDisabled);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheTableAutoScalingDescriptionNullWhenTheServiceOmitsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->updateTableReplicaAutoScaling($this->documentedRequest());

        self::assertNull($response->tableAutoScalingDescription);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testFailsOnABodyItCannotDeserialize(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"TableAutoScalingDescription":{"TableStatus":"GONE"}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->updateTableReplicaAutoScaling($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UpdateTableReplicaAutoScalingRequest(str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyListOfIndexUpdates(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UpdateTableReplicaAutoScalingRequest(
            tableName: 'Music',
            globalSecondaryIndexUpdates: new GlobalSecondaryIndexAutoScalingUpdateList(),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyListOfReplicaUpdates(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UpdateTableReplicaAutoScalingRequest(tableName: 'Music', replicaUpdates: new ReplicaAutoScalingUpdateList());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsARoleArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AutoScalingSettingsUpdate(autoScalingRoleArn: str_repeat('a', 1601));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameShorterThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GlobalSecondaryIndexAutoScalingUpdate(indexName: 'ix');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAReplicaIndexNameWithCharactersTheServiceDoesNotAllow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ReplicaGlobalSecondaryIndexAutoScalingUpdate(indexName: 'Album Title Index');
    }

    /**
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    private function createClient(MockClient $httpClient): DynamoDBClient
    {
        return new DynamoDBClient(
            'eu-central-1',
            new Credentials('AKIDEXAMPLE', 'secret'),
            $httpClient,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): UpdateTableReplicaAutoScalingRequest
    {
        return new UpdateTableReplicaAutoScalingRequest(
            tableName: 'Music',
            globalSecondaryIndexUpdates: new GlobalSecondaryIndexAutoScalingUpdateList([
                new GlobalSecondaryIndexAutoScalingUpdate(
                    indexName: 'AlbumTitleIndex',
                    provisionedWriteCapacityAutoScalingUpdate: new AutoScalingSettingsUpdate(autoScalingDisabled: true),
                ),
            ]),
            provisionedWriteCapacityAutoScalingUpdate: new AutoScalingSettingsUpdate(maximumUnits: 80, minimumUnits: 10),
            replicaUpdates: new ReplicaAutoScalingUpdateList([
                new ReplicaAutoScalingUpdate(
                    regionName: 'us-east-1',
                    replicaGlobalSecondaryIndexUpdates: new ReplicaGlobalSecondaryIndexAutoScalingUpdateList([
                        new ReplicaGlobalSecondaryIndexAutoScalingUpdate(
                            indexName: 'AlbumTitleIndex',
                            provisionedReadCapacityAutoScalingUpdate: new AutoScalingSettingsUpdate(
                                maximumUnits: 60,
                                minimumUnits: 5,
                            ),
                        ),
                    ]),
                    replicaProvisionedReadCapacityAutoScalingUpdate: new AutoScalingSettingsUpdate(
                        autoScalingRoleArn: self::ROLE_ARN,
                        maximumUnits: 200,
                        minimumUnits: 5,
                        scalingPolicyUpdate: AutoScalingPolicyUpdate::targetTracking(
                            targetValue: 60.5,
                            disableScaleIn: false,
                            scaleInCooldown: 60,
                            scaleOutCooldown: 30,
                            policyName: 'DynamoDBReadCapacityUtilization:table/Music',
                        ),
                    ),
                ),
            ]),
        );
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
