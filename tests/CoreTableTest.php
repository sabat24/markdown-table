<?php

declare(strict_types=1);

namespace Tests\sabat24\MarkdownTable;

use PHPUnit\Framework\TestCase;
use sabat24\MarkdownTable\Column;
use sabat24\MarkdownTable\Table;

class CoreTableTest extends TestCase
{
    public function testSimpleTable(): void
    {
        $t = new Table(options: ['delimiterStart' => false, 'delimiterEnd' => false]);
        $t->addColumn(0, new Column('Col.A'));

        $this->assertInstanceOf(Column::class, $t->getColumn(0));

        $expect = 'Col.A' . PHP_EOL
            . '-----' . PHP_EOL
            . 'a    ' . PHP_EOL
            . 'b    ' . PHP_EOL
            . 'c    ' . PHP_EOL;

        $this->assertEquals($expect, $t->getString([
            ['a', 'z'],
            ['b'],
            ['c', 'y', 'x'],
        ]));
    }

    public function testConstructorWithColumns(): void
    {
        $t = new Table(['first_name', 'last_name'], ['delimiterStart' => false, 'delimiterEnd' => false]);

        $expect = 'first_name | last_name   ' . PHP_EOL
            . '---------- | ------------' . PHP_EOL
            . 'Sven       | Frey        ' . PHP_EOL
            . 'Clemencia  | Tijerina    ' . PHP_EOL
            . '           | Shervashidze' . PHP_EOL;

        $this->assertEquals($expect, $t->getString([
            ['first_name' => 'Sven', 'last_name' => 'Frey'],
            ['first_name' => 'Clemencia', 'last_name' => 'Tijerina'],
            ['last_name' => 'Shervashidze'],
        ]));
    }

    /**
     * Test what happens in case a column is dropped.
     */
    public function testDroppingColumn(): void
    {
        $t = new Table(['first_name', 'last_name'], ['delimiterStart' => false, 'delimiterEnd' => false]);

        $expect = 'last_name   ' . PHP_EOL
            . '------------' . PHP_EOL
            . 'Frey        ' . PHP_EOL
            . 'Tijerina    ' . PHP_EOL
            . 'Shervashidze' . PHP_EOL;

        $t->dropColumn('first_name');

        $this->assertEquals($expect, $t->getString([
            ['first_name' => 'Sven', 'last_name' => 'Frey'],
            ['first_name' => 'Clemencia', 'last_name' => 'Tijerina'],
            ['last_name' => 'Shervashidze'],
        ]));
    }

    /**
     * Test changing a column title.
     */
    public function testChangeColumnTitle(): void
    {
        $t = new Table(['first_name', 'last_name'], ['delimiterStart' => false, 'delimiterEnd' => false]);

        $expect = 'first_name | surname     ' . PHP_EOL
            . '---------- | ------------' . PHP_EOL
            . 'Sven       | Frey        ' . PHP_EOL
            . 'Clemencia  | Tijerina    ' . PHP_EOL
            . '           | Shervashidze' . PHP_EOL;

        $t->getColumn('last_name')->setTitle('surname');

        $this->assertEquals($expect, $t->getString([
            ['first_name' => 'Sven', 'last_name' => 'Frey'],
            ['first_name' => 'Clemencia', 'last_name' => 'Tijerina'],
            ['last_name' => 'Shervashidze'],
        ]));
    }

    /**
     * Test exception when requesting a non existent column.
     */
    public function testExceptionNonExistentColumnPosition(): void
    {
        $t = new Table(['first_name']);
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Column position last_name does not exist!');
        $t->getColumn('last_name');
    }

    /**
     * Test for the exception thrown in case no columns have been defined.
     */
    public function testExceptionWithNoColumnsDefined(): void
    {
        $t = new Table();
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('No columns defined.');
        $t->getString([['first_name' => 'Sven', 'last_name' => 'Frey']]);
    }

    /**
     * Test for the exception thrown in case a one-dimensional array is provided.
     */
    public function testExceptionWithOneDimensionalArray(): void
    {
        $t = new Table(['first_name', 'last_name']);
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Rows need to be an array of arrays.');
        /**
         * @noinspection PhpParamsInspection
         * @phpstan-ignore-next-line
         */
        $t->getString(['first_name' => 'Sven', 'last_name' => 'Frey']);
    }
}
