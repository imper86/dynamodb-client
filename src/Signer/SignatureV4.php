<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Signer;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\Credentials;
use Psr\Http\Message\RequestInterface;
use RuntimeException;
use Webmozart\Assert\Assert;

use function array_keys;
use function array_map;
use function explode;
use function hash;
use function hash_hmac;
use function implode;
use function in_array;
use function ksort;
use function preg_replace;
use function rawurldecode;
use function rawurlencode;
use function sprintf;
use function strpos;
use function strtolower;
use function substr;
use function trim;
use function usort;

/**
 * Signs requests with the AWS Signature Version 4 algorithm.
 *
 * @see https://docs.aws.amazon.com/IAM/latest/UserGuide/create-signed-request.html
 */
final class SignatureV4
{
    public const ALGORITHM = 'AWS4-HMAC-SHA256';

    private const HASH_ALGORITHM = 'sha256';

    private const REQUEST_TYPE = 'aws4_request';

    private const DATE_FORMAT = 'Ymd\THis\Z';

    private const DATE_STAMP_FORMAT = 'Ymd';

    /**
     * Headers that are either hop-by-hop or rewritten by clients and proxies, signing them would break the signature.
     *
     * @var list<string>
     */
    private const UNSIGNED_HEADERS = [
        'authorization',
        'connection',
        'content-length',
        'expect',
        'keep-alive',
        'proxy-authorization',
        'te',
        'trailer',
        'transfer-encoding',
        'upgrade',
        'user-agent',
        'x-amzn-trace-id',
    ];

    /**
     * @param non-empty-string $region
     * @param non-empty-string $service
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly Credentials $credentials,
        private readonly string $region,
        private readonly string $service,
    ) {
        Assert::stringNotEmpty($this->region);
        Assert::stringNotEmpty($this->service);
    }

    /**
     * @throws InvalidArgumentException when a header value is invalid
     * @throws RuntimeException when the request body cannot be read
     */
    public function sign(RequestInterface $request, ?DateTimeImmutable $requestedAt = null): RequestInterface
    {
        $requestedAt = ($requestedAt ?? new DateTimeImmutable())->setTimezone(new DateTimeZone('UTC'));
        $amzDate = $requestedAt->format(self::DATE_FORMAT);
        $dateStamp = $requestedAt->format(self::DATE_STAMP_FORMAT);

        $request = $this->withRequiredHeaders($request, $amzDate);

        $signedHeaders = $this->signedHeaders($request);
        $canonicalRequest = $this->canonicalRequest($request, $signedHeaders);
        $scope = implode('/', [$dateStamp, $this->region, $this->service, self::REQUEST_TYPE]);

        $stringToSign = implode("\n", [
            self::ALGORITHM,
            $amzDate,
            $scope,
            hash(self::HASH_ALGORITHM, $canonicalRequest),
        ]);

        $signature = hash_hmac(self::HASH_ALGORITHM, $stringToSign, $this->signingKey($dateStamp));

        return $request->withHeader('Authorization', sprintf(
            '%s Credential=%s/%s, SignedHeaders=%s, Signature=%s',
            self::ALGORITHM,
            $this->credentials->key,
            $scope,
            implode(';', array_keys($signedHeaders)),
            $signature,
        ));
    }

    /**
     * @throws InvalidArgumentException when a header value is invalid
     */
    private function withRequiredHeaders(RequestInterface $request, string $amzDate): RequestInterface
    {
        $host = $request->getUri()->getHost();

        if ('' !== $host) {
            $port = $request->getUri()->getPort();
            $request = $request->withHeader('Host', null === $port ? $host : sprintf('%s:%d', $host, $port));
        }

        $request = $request->withHeader('X-Amz-Date', $amzDate);

        if (null !== $this->credentials->token) {
            return $request->withHeader('X-Amz-Security-Token', $this->credentials->token);
        }

        return $request;
    }

    /**
     * @return array<string, string> canonical header values indexed by lowercased header name, sorted by name
     */
    private function signedHeaders(RequestInterface $request): array
    {
        $headers = [];

        foreach ($request->getHeaders() as $header => $values) {
            $name = strtolower((string) $header);

            if (in_array($name, self::UNSIGNED_HEADERS, true)) {
                continue;
            }

            $headers[$name] = implode(',', array_map(
                static fn(string $value): string => (string) preg_replace('/\s+/', ' ', trim($value)),
                $values,
            ));
        }

        ksort($headers);

        return $headers;
    }

    /**
     * @param array<string, string> $signedHeaders
     * @throws RuntimeException when the request body cannot be read
     */
    private function canonicalRequest(RequestInterface $request, array $signedHeaders): string
    {
        $canonicalHeaders = '';

        foreach ($signedHeaders as $name => $value) {
            $canonicalHeaders .= sprintf("%s:%s\n", $name, $value);
        }

        return implode("\n", [
            $request->getMethod(),
            $this->canonicalPath($request->getUri()->getPath()),
            $this->canonicalQuery($request->getUri()->getQuery()),
            $canonicalHeaders,
            implode(';', array_keys($signedHeaders)),
            hash(self::HASH_ALGORITHM, $this->payload($request)),
        ]);
    }

    private function canonicalPath(string $path): string
    {
        if ('' === $path) {
            return '/';
        }

        return implode('/', array_map(
            static fn(string $segment): string => rawurlencode(rawurldecode($segment)),
            explode('/', $path),
        ));
    }

    private function canonicalQuery(string $query): string
    {
        if ('' === $query) {
            return '';
        }

        $parameters = [];

        foreach (explode('&', $query) as $parameter) {
            if ('' === $parameter) {
                continue;
            }

            $separator = strpos($parameter, '=');
            $name = false === $separator ? $parameter : substr($parameter, 0, $separator);
            $value = false === $separator ? '' : substr($parameter, $separator + 1);

            $parameters[] = [rawurlencode(rawurldecode($name)), rawurlencode(rawurldecode($value))];
        }

        usort($parameters, static fn(array $left, array $right): int => $left <=> $right);

        return implode('&', array_map(
            static fn(array $parameter): string => sprintf('%s=%s', $parameter[0], $parameter[1]),
            $parameters,
        ));
    }

    /**
     * @throws RuntimeException when the request body cannot be read
     */
    private function payload(RequestInterface $request): string
    {
        $body = $request->getBody();

        if (!$body->isSeekable()) {
            return $body->getContents();
        }

        $body->rewind();
        $payload = $body->getContents();
        $body->rewind();

        return $payload;
    }

    private function signingKey(string $dateStamp): string
    {
        $key = hash_hmac(self::HASH_ALGORITHM, $dateStamp, 'AWS4' . $this->credentials->secret, true);
        $key = hash_hmac(self::HASH_ALGORITHM, $this->region, $key, true);
        $key = hash_hmac(self::HASH_ALGORITHM, $this->service, $key, true);

        return hash_hmac(self::HASH_ALGORITHM, self::REQUEST_TYPE, $key, true);
    }
}
