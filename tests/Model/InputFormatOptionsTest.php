<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Model;

use Imper86\DynamoDBClient\Model\CsvOptions;
use Imper86\DynamoDBClient\Model\InputFormatOptions;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(InputFormatOptions::class)]
final class InputFormatOptionsTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsCsvOptions(): void
    {
        $csv = InputFormatOptions::csv(';', ['Artist', 'SongTitle'])->csv;

        self::assertInstanceOf(CsvOptions::class, $csv);
        self::assertSame(';', $csv->delimiter);
        self::assertSame(['Artist', 'SongTitle'], $csv->headerList?->toArray());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testLeavesOutTheHeaderListWhenNoneIsGiven(): void
    {
        $csv = InputFormatOptions::csv()->csv;

        self::assertInstanceOf(CsvOptions::class, $csv);
        self::assertNull($csv->delimiter);
        self::assertNull($csv->headerList);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testDropsTheKeysOfTheHeaderList(): void
    {
        $csv = InputFormatOptions::csv(headerList: ['first' => 'Artist', 'second' => 'SongTitle'])->csv;

        self::assertInstanceOf(CsvOptions::class, $csv);
        self::assertSame(['Artist', 'SongTitle'], $csv->headerList?->toArray());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testForwardsTheHeaderListToTheConstructorForValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);

        InputFormatOptions::csv(headerList: []);
    }
}
