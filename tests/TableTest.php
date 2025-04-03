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
     * @param array{ alignDelimiters?: bool, delimiterStart?: bool, delimiterEnd?: bool, padding?: bool, autoHeaders?: bool, headerSeparatorPadding?: bool, allowedTags?: array<string> } $options
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
     * @param array<string> $columns
     * @param array{ alignDelimiters?: bool, delimiterStart?: bool, delimiterEnd?: bool, padding?: bool, autoHeaders?: bool, headerSeparatorPadding?: bool, allowedTags?: array<string> } $options
     * @param array<array<string>> $data
     * @param string $expected
     */
    #[DataProvider('htmlTagsDataProvider')]
    public function testTableWithHtmlTags(
        array $columns,
        array $options,
        array $data,
        string $expected,
    ): void {
        $table = new Table($columns, options: $options);
        $this->assertEquals($expected, $table->getString($data));
    }

    /**
     * @return array<string, array{
     *     columns: array<string>,
     *     options: array<string, mixed>,
     *     data: array<array<string>>,
     *     expected: string
     * }>
     */
    public static function htmlTagsDataProvider(): array
    {
        return [
            'with allowed br tag' => [
                'columns' => ['Col.A', 'Col.B', 'Col.C'],
                'options' => ['allowedTags' => ['br']],
                'data' => [
                    ['a', '<br />', 'z'],
                    ['<br/>'],
                    ['c', 'y', 'some long line<br>second row'],
                ],
                'expected' => '| Col.A | Col.B  | Col.C                        |' . PHP_EOL
                    . '|-------|--------|------------------------------|' . PHP_EOL
                    . '| a     | <br /> | z                            |' . PHP_EOL
                    . '| <br/> |        |                              |' . PHP_EOL
                    . '| c     | y      | some long line<br>second row |' . PHP_EOL,
            ],
            'with escaped HTML tags' => [
                'columns' => ['Col.A', 'Col.B', 'Col.C'],
                'options' => [],
                'data' => [
                    ['a', '<br />', 'z'],
                    ['<br/>'],
                    ['c', 'y', 'some long line<br>second row'],
                ],
                'expected' => '| Col.A       | Col.B        | Col.C                              |' . PHP_EOL
                    . '|-------------|--------------|------------------------------------|' . PHP_EOL
                    . '| a           | &lt;br /&gt; | z                                  |' . PHP_EOL
                    . '| &lt;br/&gt; |              |                                    |' . PHP_EOL
                    . '| c           | y            | some long line&lt;br&gt;second row |' . PHP_EOL,
            ],
        ];
    }

    /**
     * @param string[] $allowedTags
     */
    #[DataProvider('sanitizeWithAllowedTagsDataProvider')]
    public function testSanitizeWithAllowedTags(string $value, array $allowedTags, string $expected): void
    {
        $t = new Table(options: ['allowedTags' => $allowedTags]);
        $sanitizedValue = $t->sanitizeWithAllowedTags($value);

        $this->assertEquals($expected, $sanitizedValue);
    }

    /**
     * @return array<string, array{
     *     value: string,
     *     allowedTags: string[],
     *     expected: string
     * }>
     */
    public static function sanitizeWithAllowedTagsDataProvider(): array
    {
        return [
            'simple standalone gt with quotes' => [
                'value' => 'lorem "quoted" -> ipsum',
                'allowedTags' => [],
                'expected' => 'lorem "quoted" -> ipsum',
            ],
            'Self closing nested attribute' => [
                'value' => '<strong>First line<br/>Second line</strong>',
                'allowedTags' => ['br', 'strong'],
                'expected' => '<strong>First line<br/>Second line</strong>',
            ],
            'Nested attributes, with duplicated single one' => [
                'value' => '<p><strong>Paragraph bold text</strong></p> and<strong>strong text</strong>',
                'allowedTags' => ['p', 'strong'],
                'expected' => '<p><strong>Paragraph bold text</strong></p> and<strong>strong text</strong>',
            ],
            'HTML tags with attributes' => [
                'value' => '<a href="https://example.com" target="_blank">External link</a>',
                'allowedTags' => ['a'],
                'expected' => '<a href="https://example.com" target="_blank">External link</a>',
            ],
            'Mixed allowed and disallowed tags' => [
                'value' => '<p>This is <strong>important</strong> and <em>emphasized</em> text</p>',
                'allowedTags' => ['p', 'strong'],
                'expected' => '<p>This is <strong>important</strong> and &lt;em&gt;emphasized&lt;/em&gt; text</p>',
            ],
            'Self-closing tags with attributes' => [
                'value' => 'Image: <img src="https://image.jpg" alt="description" /> in text',
                'allowedTags' => ['img'],
                'expected' => 'Image: <img src="https://image.jpg" alt="description" /> in text',
            ],
            'Multiple nested levels' => [
                'value' => '<div><p><span>Deeply <strong>nested</strong> content</span></p></div>',
                'allowedTags' => ['div', 'p', 'span', 'strong'],
                'expected' => '<div><p><span>Deeply <strong>nested</strong> content</span></p></div>',
            ],
            'HTML entities within tags' => [
                'value' => '<code>&lt;script&gt;alert("test");&lt;/script&gt;</code>',
                'allowedTags' => ['code'],
                'expected' => '<code>&amp;lt;script&amp;gt;alert("test");&amp;lt;/script&amp;gt;</code>',
            ],
            'Case sensitivity test' => [
                'value' => '<STRONG>Uppercase tag</STRONG> and <strong>lowercase tag</strong>',
                'allowedTags' => ['strong'],
                'expected' => '<STRONG>Uppercase tag</STRONG> and <strong>lowercase tag</strong>',
            ],
            'Only some tags allowed' => [
                'value' => '<div>Container with <span>span</span> and <i>italic</i> and <b>bold</b></div>',
                'allowedTags' => ['div', 'b'],
                'expected' => '<div>Container with &lt;span&gt;span&lt;/span&gt; and &lt;i&gt;italic&lt;/i&gt; and <b>bold</b></div>',
            ],
        ];
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
