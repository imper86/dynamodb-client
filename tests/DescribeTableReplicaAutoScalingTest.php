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
use Imper86\DynamoDBClient\Message\DescribeTableReplicaAutoScalingRequest;
use Imper86\DynamoDBClient\Model\AutoScalingPolicyDescription;
use Imper86\DynamoDBClient\Model\AutoScalingPolicyDescriptionList;
use Imper86\DynamoDBClient\Model\AutoScalingSettingsDescription;
use Imper86\DynamoDBClient\Model\AutoScalingTargetTrackingScalingPolicyConfigurationDescription;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\IndexStatus;
use Imper86\DynamoDBClient\Model\ReplicaAutoScalingDescription;
use Imper86\DynamoDBClient\Model\ReplicaAutoScalingDescriptionList;
use Imper86\DynamoDBClient\Model\ReplicaGlobalSecondaryIndexAutoScalingDescription;
use Imper86\DynamoDBClient\Model\ReplicaGlobalSecondaryIndexAutoScalingDescriptionList;
use Imper86\DynamoDBClient\Model\ReplicaStatus;
use Imper86\DynamoDBClient\Model\TableAutoScalingDescription;
use Imper86\DynamoDBClient\Model\TableStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The DescribeTableReplicaAutoScaling reference has no Examples section, so the fixtures are built from
 * its request and response syntax, describing a `Music` global table with an auto scaled replica in
 * `us-east-1`, including one global secondary index, and a second replica still being created in
 * `eu-west-1`.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_DescribeTableReplicaAutoScaling.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class DescribeTableReplicaAutoScalingTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/describe-table-replica-auto-scaling-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/describe-table-replica-auto-scaling-response.json';

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

        $this->createClient($httpClient)->describeTableReplicaAutoScaling(
            new DescribeTableReplicaAutoScalingRequest('Music'),
        );

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.DescribeTableReplicaAutoScaling', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheTableAndItsReplicas(): void
    {
        $table = $this->describeDocumentedTable();

        self::assertSame('Music', $table->tableName);
        self::assertSame(TableStatus::ACTIVE, $table->tableStatus);

        $replicas = $table->replicas;

        self::assertInstanceOf(ReplicaAutoScalingDescriptionList::class, $replicas);
        self::assertCount(2, $replicas);
        self::assertSame('us-east-1', $replicas->get(0)?->regionName);
        self::assertSame(ReplicaStatus::ACTIVE, $replicas->get(0)?->replicaStatus);
        self::assertSame('eu-west-1', $replicas->get(1)?->regionName);
        self::assertSame(ReplicaStatus::CREATING, $replicas->get(1)?->replicaStatus);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheAutoScalingSettingsOfAReplica(): void
    {
        $replica = $this->describeDocumentedTable()->replicas?->get(0);

        self::assertInstanceOf(ReplicaAutoScalingDescription::class, $replica);

        $read = $replica->replicaProvisionedReadCapacityAutoScalingSettings;

        self::assertInstanceOf(AutoScalingSettingsDescription::class, $read);
        self::assertFalse($read->autoScalingDisabled);
        self::assertSame(
            'arn:aws:iam::123456789012:role/aws-service-role/dynamodb.application-autoscaling.amazonaws.com/'
            . 'AWSServiceRoleForApplicationAutoScaling_DynamoDBTable',
            $read->autoScalingRoleArn,
        );
        self::assertSame(100, $read->maximumUnits);
        self::assertSame(5, $read->minimumUnits);

        $write = $replica->replicaProvisionedWriteCapacityAutoScalingSettings;

        self::assertInstanceOf(AutoScalingSettingsDescription::class, $write);
        self::assertSame(50, $write->maximumUnits);

        $policies = $write->scalingPolicies;

        self::assertInstanceOf(AutoScalingPolicyDescriptionList::class, $policies);
        self::assertCount(1, $policies);

        $policy = $policies->get(0);

        self::assertInstanceOf(AutoScalingPolicyDescription::class, $policy);
        self::assertSame('DynamoDBWriteCapacityUtilization:table/Music', $policy->policyName);

        $configuration = $policy->targetTrackingScalingPolicyConfiguration;

        self::assertInstanceOf(AutoScalingTargetTrackingScalingPolicyConfigurationDescription::class, $configuration);
        self::assertTrue($configuration->disableScaleIn);
        self::assertSame(120, $configuration->scaleInCooldown);
        self::assertSame(30, $configuration->scaleOutCooldown);
        self::assertSame(55.5, $configuration->targetValue);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheAutoScalingSettingsOfAGlobalSecondaryIndex(): void
    {
        $indexes = $this->describeDocumentedTable()->replicas?->get(0)?->globalSecondaryIndexes;

        self::assertInstanceOf(ReplicaGlobalSecondaryIndexAutoScalingDescriptionList::class, $indexes);
        self::assertCount(1, $indexes);

        $index = $indexes->get(0);

        self::assertInstanceOf(ReplicaGlobalSecondaryIndexAutoScalingDescription::class, $index);
        self::assertSame('AlbumTitleIndex', $index->indexName);
        self::assertSame(IndexStatus::ACTIVE, $index->indexStatus);

        $read = $index->provisionedReadCapacityAutoScalingSettings;

        self::assertInstanceOf(AutoScalingSettingsDescription::class, $read);
        self::assertSame(40, $read->maximumUnits);
        self::assertSame(
            70.0,
            $read->scalingPolicies?->get(0)?->targetTrackingScalingPolicyConfiguration?->targetValue,
        );

        $write = $index->provisionedWriteCapacityAutoScalingSettings;

        self::assertInstanceOf(AutoScalingSettingsDescription::class, $write);
        self::assertTrue($write->autoScalingDisabled);
        self::assertSame(10, $write->maximumUnits);
        self::assertSame(1, $write->minimumUnits);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheElementsTheServiceOmitsNull(): void
    {
        $replicas = $this->describeDocumentedTable()->replicas;

        self::assertInstanceOf(ReplicaAutoScalingDescriptionList::class, $replicas);

        $creating = $replicas->get(1);

        self::assertInstanceOf(ReplicaAutoScalingDescription::class, $creating);
        self::assertNull($creating->globalSecondaryIndexes);
        self::assertNull($creating->replicaProvisionedReadCapacityAutoScalingSettings);
        self::assertNull($creating->replicaProvisionedWriteCapacityAutoScalingSettings);

        $write = $replicas->get(0)?->globalSecondaryIndexes?->get(0)?->provisionedWriteCapacityAutoScalingSettings;

        self::assertInstanceOf(AutoScalingSettingsDescription::class, $write);
        self::assertNull($write->autoScalingRoleArn);
        self::assertNull($write->scalingPolicies);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testAcceptsAWholeNumberTargetValue(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: <<<'JSON'
            {"TableAutoScalingDescription":{"Replicas":[{"ReplicaProvisionedReadCapacityAutoScalingSettings":
            {"ScalingPolicies":[{"TargetTrackingScalingPolicyConfiguration":{"TargetValue":70}}]}}]}}
            JSON));

        $response = $this->createClient($httpClient)->describeTableReplicaAutoScaling(
            new DescribeTableReplicaAutoScalingRequest('Music'),
        );

        self::assertSame(
            70.0,
            $response->tableAutoScalingDescription?->replicas?->get(0)
                ?->replicaProvisionedReadCapacityAutoScalingSettings?->scalingPolicies?->get(0)
                ?->targetTrackingScalingPolicyConfiguration?->targetValue,
        );
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

        $response = $this->createClient($httpClient)->describeTableReplicaAutoScaling(
            new DescribeTableReplicaAutoScalingRequest('Music'),
        );

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
        $httpClient->addResponse(new Response(body: '{"TableAutoScalingDescription":{"Replicas":[{"ReplicaStatus":"GONE"}]}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->describeTableReplicaAutoScaling(
            new DescribeTableReplicaAutoScalingRequest('Music'),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DescribeTableReplicaAutoScalingRequest(str_repeat('a', 1025));
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    private function describeDocumentedTable(): TableAutoScalingDescription
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->describeTableReplicaAutoScaling(
            new DescribeTableReplicaAutoScalingRequest('Music'),
        );

        $table = $response->tableAutoScalingDescription;

        self::assertInstanceOf(TableAutoScalingDescription::class, $table);

        return $table;
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

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
