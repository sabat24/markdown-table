<?php

declare(strict_types=1);

namespace Tests\sabat24\MarkdownTable;

use PHPUnit\Framework\Attributes\DataProvider;
use sabat24\MarkdownTable\Column;
use sabat24\MarkdownTable\Table;
use PHPUnit\Framework\TestCase;

class TableTest extends TestCase
{
    public function testSimpleTable(): void
    {
        $t = new Table(options: ['headerSeparatorPadding' => true]);
        $t->addColumn(0, new Column('Col.A'));

        $this->assertInstanceOf(Column::class, $t->getColumn(0));

        $expect = '| Col.A |' . PHP_EOL
            . '| ----- |' . PHP_EOL
            . '| a     |' . PHP_EOL
            . '| b     |' . PHP_EOL
            . '| c     |' . PHP_EOL;

        $this->assertEquals($expect, $t->getString([
            ['a', 'z'],
            ['b'],
            ['c', 'y', 'x'],
        ]));
    }

    public function testTableWithColumnsConstructor(): void
    {
        $t = new Table(['Branch', 'Commit']);

        $expect = '| Branch  | Commit           |' . PHP_EOL
            . '|---------|------------------|' . PHP_EOL
            . '| main    | 0123456789abcdef |' . PHP_EOL
            . '| staging | fedcba9876543210 |' . PHP_EOL;

        $this->assertEquals($expect, $t->getString([
            ['main', '0123456789abcdef'],
            ['staging', 'fedcba9876543210'],
        ]));
    }

    public function testTableWithAutoHeaders(): void
    {
        $t = new Table(options: ['autoHeaders' => true, 'headerSeparatorPadding' => true]);

        $expect = '| Col.A | Col.B | Col.C |' . PHP_EOL
            . '| ----- | ----- | ----- |' . PHP_EOL
            . '| a     | z     |       |' . PHP_EOL
            . '| b     |       |       |' . PHP_EOL
            . '| c     | y     | x     |' . PHP_EOL;

        $this->assertEquals($expect, $t->getString([
            ['Col.A', 'Col.B', 'Col.C'],
            ['a', 'z'],
            ['b'],
            ['c', 'y', 'x'],
        ]));
    }

    /**
     * Data provider for testTablePaddings
     *
     * @return array<string, array{options: array<string, bool>, alignment: string, expected: string}>
     */
    public static function tableOptionsProvider(): array
    {
        return [
            'no padding, left alignment' => [
                'options' => [
                    'padding' => false,
                    'headerSeparatorPadding' => false,
                    'delimiterStart' => true,
                    'delimiterEnd' => true,
                ],
                'alignment' => 'l',
                'expected' => '|Col.A|Col.B|Col.C|' . PHP_EOL
                    . '|-----|-----|-----|' . PHP_EOL
                    . '|a    |z    |     |' . PHP_EOL
                    . '|b    |     |     |' . PHP_EOL
                    . '|c    |y    |x    |' . PHP_EOL,
            ],
            'with padding, left alignment' => [
                'options' => [
                    'padding' => true,
                    'headerSeparatorPadding' => true,
                    'delimiterStart' => true,
                    'delimiterEnd' => true,
                ],
                'alignment' => 'l',
                'expected' => '| Col.A | Col.B | Col.C |' . PHP_EOL
                    . '| ----- | ----- | ----- |' . PHP_EOL
                    . '| a     | z     |       |' . PHP_EOL
                    . '| b     |       |       |' . PHP_EOL
                    . '| c     | y     | x     |' . PHP_EOL,
            ],
            'with padding, no delimiters, left alignment' => [
                'options' => [
                    'padding' => true,
                    'headerSeparatorPadding' => true,
                    'delimiterStart' => false,
                    'delimiterEnd' => false,
                ],
                'alignment' => 'l',
                'expected' => 'Col.A | Col.B | Col.C' . PHP_EOL
                    . '----- | ----- | -----' . PHP_EOL
                    . 'a     | z     |      ' . PHP_EOL
                    . 'b     |       |      ' . PHP_EOL
                    . 'c     | y     | x    ' . PHP_EOL,
            ],
            'with padding except header separator, left alignment' => [
                'options' => [
                    'padding' => true,
                    'headerSeparatorPadding' => false,
                    'delimiterStart' => true,
                    'delimiterEnd' => true,
                ],
                'alignment' => 'l',
                'expected' => '| Col.A | Col.B | Col.C |' . PHP_EOL
                    . '|-------|-------|-------|' . PHP_EOL
                    . '| a     | z     |       |' . PHP_EOL
                    . '| b     |       |       |' . PHP_EOL
                    . '| c     | y     | x     |' . PHP_EOL,
            ],
            'no padding, right alignment' => [
                'options' => [
                    'padding' => false,
                    'headerSeparatorPadding' => false,
                    'delimiterStart' => true,
                    'delimiterEnd' => true,
                ],
                'alignment' => 'r',
                'expected' => '|Col.A|Col.B|Col.C|' . PHP_EOL
                    . '|----:|----:|----:|' . PHP_EOL
                    . '|    a|    z|     |' . PHP_EOL
                    . '|    b|     |     |' . PHP_EOL
                    . '|    c|    y|    x|' . PHP_EOL,
            ],
            'no padding, center alignment' => [
                'options' => [
                    'padding' => false,
                    'headerSeparatorPadding' => false,
                    'delimiterStart' => true,
                    'delimiterEnd' => true,
                ],
                'alignment' => 'c',
                'expected' => '|Col.A|Col.B|Col.C|' . PHP_EOL
                    . '|:---:|:---:|:---:|' . PHP_EOL
                    . '|  a  |  z  |     |' . PHP_EOL
                    . '|  b  |     |     |' . PHP_EOL
                    . '|  c  |  y  |  x  |' . PHP_EOL,
            ],
        ];
    }

    /**
     * @param array<string, bool> $options
     * @param string $alignment
     * @param string $expected
     */
    #[DataProvider('tableOptionsProvider')] public function testTablePaddings(
        array $options,
        string $alignment,
        string $expected,
    ): void {
        $t = new Table(options: ['autoHeaders' => true, 'headerSeparatorPadding' => true]);

        $t->setOptions($options);
        $t->setAlignment($alignment);

        $this->assertEquals($expected, $t->getString([
            ['Col.A', 'Col.B', 'Col.C'],
            ['a', 'z'],
            ['b'],
            ['c', 'y', 'x'],
        ]));
    }

    public function testTableAssociativeNonExistingWithAutoHeaders(): void
    {
        $t = new Table(options: ['autoHeaders' => true, 'headerSeparatorPadding' => true]);

        $expect = '| Col.A | Col.B | Col.C |' . PHP_EOL
            . '| ----- | ----- | ----- |' . PHP_EOL
            . '| a     |       |       |' . PHP_EOL
            . '| b     |       |       |' . PHP_EOL
            . '| c     |       | x     |' . PHP_EOL;

        $this->assertEquals($expect, $t->getString([
            ['Col.A', 4 => 'Col.B', 2 => 'Col.C'],
            ['a', 'z'],
            ['b'],
            ['c', 'y', 'x'],
        ]));
    }

    /**
     * Test for the exception thrown in case a one-dimensional array is provided with autoHeaders enabled.
     */
    public function testExceptionWithOneDimensionalArray(): void
    {
        $t = new Table(options: ['autoHeaders' => true]);
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Rows need to be an array of arrays.');
        /**
         * @noinspection PhpParamsInspection
         * @phpstan-ignore-next-line
         */
        $t->getString(['first_name' => 'Sven', 'last_name' => 'Frey']);
    }
}
