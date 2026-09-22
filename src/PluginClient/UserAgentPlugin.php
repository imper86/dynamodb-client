<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\PluginClient;

use Composer\InstalledVersions;
use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use InvalidArgumentException;
use OutOfBoundsException;
use Psr\Http\Message\RequestInterface;
use Webmozart\Assert\Assert;

use function array_merge;
use function function_exists;
use function getenv;
use function implode;
use function is_string;
use function php_uname;
use function preg_replace;
use function sprintf;
use function strtolower;

/**
 * Identifies this client in the `User-Agent` header, following the AWS user agent format.
 *
 * The header is built from space separated `prefix/name#value` fields, in the order AWS expects them:
 * the sdk name and version first, then the format version, the targeted api, the operating system,
 * the language, the execution environment and, last, the application id.
 *
 * Only characters allowed in an http token are emitted, everything else is replaced with a dash.
 *
 * @see https://docs.aws.amazon.com/sdkref/latest/guide/feature-appid.html
 */
final readonly class UserAgentPlugin implements Plugin
{
    /**
     * Name this client reports itself under, the package name with the vendor separator replaced.
     */
    public const string SDK_NAME = 'imper86-dynamodb-client';

    public const string PACKAGE_NAME = 'imper86/dynamodb-client';

    /**
     * Version of the AWS user agent format implemented here.
     */
    public const string USER_AGENT_VERSION = '2.1';

    public const string APP_ID_ENV_VARIABLE = 'AWS_SDK_UA_APP_ID';

    public const string EXECUTION_ENV_VARIABLE = 'AWS_EXECUTION_ENV';

    public const int APP_ID_MAX_LENGTH = 50;

    private const string HEADER = 'User-Agent';

    private const string SERVICE_ID = 'dynamodb';

    private const string UNKNOWN_VERSION = 'unknown';

    /**
     * Operating systems AWS recognises, indexed by php's own family name. Anything else is
     * reported as `os/other`, with the raw name kept in an additional `md` field.
     *
     * @var array<string, string>
     */
    private const array OS_FAMILIES = [
        'Darwin' => 'macos',
        'Linux' => 'linux',
        'Windows' => 'windows',
    ];

    /**
     * Characters AWS does not allow in a field, `#` is allowed in values only.
     */
    private const string DISALLOWED_CHARACTERS = '/[^0-9A-Za-z!$%&\'*+\-.^_`|~,]/';

    private const string DISALLOWED_CHARACTERS_IN_VALUE = '/[^0-9A-Za-z!$%&\'*+\-.^_`|~,#]/';

    private string $userAgent;

    /**
     * @param null|non-empty-string $appId an opaque identifier of your application, falls back to
     *                                     the AWS_SDK_UA_APP_ID environment variable
     * @throws InvalidArgumentException when the application id is longer than 50 characters
     */
    public function __construct(?string $appId = null)
    {
        Assert::nullOrStringNotEmpty($appId);

        $this->userAgent = implode(' ', array_merge(
            $this->sdkMetadata(),
            $this->osMetadata(),
            $this->languageMetadata(),
            $this->executionEnvironmentMetadata(),
            $this->appIdMetadata($appId ?? $this->readEnvVariable(self::APP_ID_ENV_VARIABLE)),
        ));
    }

    /**
     * @throws InvalidArgumentException when a header value is invalid
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        $current = $request->getHeaderLine(self::HEADER);

        return $next($request->withHeader(
            self::HEADER,
            '' === $current ? $this->userAgent : sprintf('%s %s', $this->userAgent, $current),
        ));
    }

    /**
     * @return list<string>
     */
    private function sdkMetadata(): array
    {
        $version = $this->packageVersion();

        return [
            $this->field(self::SDK_NAME, $version),
            $this->field('ua', self::USER_AGENT_VERSION),
            $this->field('api', self::SERVICE_ID, $version),
        ];
    }

    /**
     * @return list<string>
     */
    private function osMetadata(): array
    {
        $name = $this->systemInformation('s');
        $version = $this->systemInformation('r');
        $family = self::OS_FAMILIES[PHP_OS_FAMILY] ?? null;

        $metadata = null === $family
            ? [$this->field('os', 'other'), $this->field('md', $name ?? PHP_OS_FAMILY, $version)]
            : [$this->field('os', $family, $version)];

        $architecture = $this->systemInformation('m');

        if (null !== $architecture) {
            $metadata[] = $this->field('md', 'arch', strtolower($architecture));
        }

        return $metadata;
    }

    /**
     * @return list<string>
     */
    private function languageMetadata(): array
    {
        return [$this->field('lang', 'php', PHP_VERSION)];
    }

    /**
     * @return list<string>
     */
    private function executionEnvironmentMetadata(): array
    {
        $executionEnvironment = $this->readEnvVariable(self::EXECUTION_ENV_VARIABLE);

        if (null === $executionEnvironment) {
            return [];
        }

        return [$this->field('exec-env', $executionEnvironment)];
    }

    /**
     * @param null|non-empty-string $appId
     * @return list<string>
     * @throws InvalidArgumentException when the application id is longer than 50 characters
     */
    private function appIdMetadata(?string $appId): array
    {
        if (null === $appId) {
            return [];
        }

        Assert::maxLength($appId, self::APP_ID_MAX_LENGTH);

        return [$this->field('app', $appId)];
    }

    /**
     * Builds a single `prefix/name#value` field, `#value` is left out when there is no value.
     */
    private function field(string $prefix, string $name, ?string $value = null): string
    {
        $field = sprintf('%s/%s', $this->sanitize($prefix, true), $this->sanitize($name));

        if (null === $value || '' === $value) {
            return $field;
        }

        return sprintf('%s#%s', $field, $this->sanitize($value, true));
    }

    private function sanitize(string $value, bool $allowHash = false): string
    {
        $pattern = $allowHash ? self::DISALLOWED_CHARACTERS_IN_VALUE : self::DISALLOWED_CHARACTERS;

        return (string) preg_replace($pattern, '-', $value);
    }

    private function packageVersion(): string
    {
        try {
            return InstalledVersions::getPrettyVersion(self::PACKAGE_NAME) ?? self::UNKNOWN_VERSION;
        } catch (OutOfBoundsException) {
            return self::UNKNOWN_VERSION;
        }
    }

    /**
     * @return null|non-empty-string
     */
    private function systemInformation(string $mode): ?string
    {
        if (!function_exists('php_uname')) {
            return null;
        }

        $value = php_uname($mode);

        return '' === $value ? null : $value;
    }

    /**
     * @return null|non-empty-string
     */
    private function readEnvVariable(string $name): ?string
    {
        $value = getenv($name);

        if (!is_string($value) || '' === $value) {
            return null;
        }

        return $value;
    }
}
