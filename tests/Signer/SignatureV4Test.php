<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Signer;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Signer\SignatureV4;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(SignatureV4::class)]
final class SignatureV4Test extends TestCase
{
    private const KEY = 'AKIDEXAMPLE';

    private const SECRET = 'wJalrXUtnFEMI/K7MDENG+bPxRfiCYEXAMPLEKEY';

    /**
     * @return iterable<string, array{RequestInterface, string, string}>
     */
    public static function provideSignsRequestsOfAwsTestSuiteCases(): iterable
    {
        yield 'get-vanilla' => [
            new Request('GET', 'https://example.amazonaws.com/'),
            'host;x-amz-date',
            '5fa00fa31553b73ebf1942676e86291e8372ff2a2260956d9b8aae1d763fbf31',
        ];

        yield 'get-vanilla-query-order-key' => [
            new Request('GET', 'https://example.amazonaws.com/?Param2=value2&Param1=value1'),
            'host;x-amz-date',
            'b97d918cfa904a5beff61c982a1b6f458b799221646efd99d3219ec94cdf2500',
        ];

        yield 'post-vanilla' => [
            new Request('POST', 'https://example.amazonaws.com/'),
            'host;x-amz-date',
            '5da7c1a2acd57cee7505fc6676e4e544621c30862966e37dddb68e92efbe5d6b',
        ];

        yield 'post-x-www-form-urlencoded' => [
            new Request(
                'POST',
                'https://example.amazonaws.com/',
                ['Content-Type' => 'application/x-www-form-urlencoded'],
                'Param1=value1',
            ),
            'content-type;host;x-amz-date',
            'ff11897932ad3f4e8b18135d722051e5ac45fc38421b1da7b9d196a0fe09473a',
        ];
    }

    /**
     * Test vectors of the official AWS Signature Version 4 test suite.
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @see https://docs.aws.amazon.com/IAM/latest/UserGuide/create-signed-request.html
     */
    #[DataProvider('provideSignsRequestsOfAwsTestSuiteCases')]
    public function testSignsRequestsOfAwsTestSuite(
        RequestInterface $request,
        string $signedHeaders,
        string $signature,
    ): void {
        $request = $this->signer()->sign($request, $this->requestedAt());

        self::assertSame('20150830T123600Z', $request->getHeaderLine('X-Amz-Date'));
        self::assertSame('example.amazonaws.com', $request->getHeaderLine('Host'));
        self::assertSame(
            sprintf(
                'AWS4-HMAC-SHA256 Credential=%s/20150830/us-east-1/service/aws4_request, '
                . 'SignedHeaders=%s, Signature=%s',
                self::KEY,
                $signedHeaders,
                $signature,
            ),
            $request->getHeaderLine('Authorization'),
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testSignsSessionTokenOfTemporaryCredentials(): void
    {
        $signer = new SignatureV4(
            new Credentials(self::KEY, self::SECRET, 'FwoGZXIvYXdzEBYaD'),
            'us-east-1',
            'service',
        );

        $request = $signer->sign(new Request('GET', 'https://example.amazonaws.com/'), $this->requestedAt());

        self::assertSame('FwoGZXIvYXdzEBYaD', $request->getHeaderLine('X-Amz-Security-Token'));
        self::assertStringContainsString(
            'SignedHeaders=host;x-amz-date;x-amz-security-token',
            $request->getHeaderLine('Authorization'),
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testDoesNotSignHeadersRewrittenByClientsAndProxies(): void
    {
        $request = new Request('POST', 'https://example.amazonaws.com/', [
            'Content-Length' => '13',
            'User-Agent' => 'imper86/1.0',
            'X-Amzn-Trace-Id' => 'Root=1-63441c4a',
            'X-Amz-Target' => 'DynamoDB_20120810.GetItem',
        ], 'Param1=value1');

        $signed = $this->signer()->sign($request, $this->requestedAt());

        self::assertStringContainsString(
            'SignedHeaders=host;x-amz-date;x-amz-target',
            $signed->getHeaderLine('Authorization'),
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testSignatureCoversTheRequestBody(): void
    {
        $signer = $this->signer();
        $uri = 'https://example.amazonaws.com/';

        $first = $signer->sign(new Request('POST', $uri, [], '{"TableName":"Thread"}'), $this->requestedAt());
        $second = $signer->sign(new Request('POST', $uri, [], '{"TableName":"Forum"}'), $this->requestedAt());

        self::assertNotSame($first->getHeaderLine('Authorization'), $second->getHeaderLine('Authorization'));
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testRewindsTheRequestBodyAfterSigning(): void
    {
        $request = new Request('POST', 'https://example.amazonaws.com/', [], '{"TableName":"Thread"}');

        $signed = $this->signer()->sign($request, $this->requestedAt());

        self::assertSame('{"TableName":"Thread"}', $signed->getBody()->getContents());
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testDefaultsToTheCurrentTimeAndConvertsItToUtc(): void
    {
        $request = $this->signer()->sign(
            new Request('GET', 'https://example.amazonaws.com/'),
            new DateTimeImmutable('2015-08-30 14:36:00', new DateTimeZone('Europe/Warsaw')),
        );

        self::assertSame('20150830T123600Z', $request->getHeaderLine('X-Amz-Date'));
    }

    /**
     * @throws InvalidArgumentException
     */
    private function signer(): SignatureV4
    {
        return new SignatureV4(new Credentials(self::KEY, self::SECRET), 'us-east-1', 'service');
    }

    private function requestedAt(): DateTimeImmutable
    {
        return new DateTimeImmutable('2015-08-30 12:36:00', new DateTimeZone('UTC'));
    }
}
