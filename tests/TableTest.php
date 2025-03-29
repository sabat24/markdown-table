<?php

declare(strict_types=1);

namespace Tests\sabat24\MarkdownTable;

use sabat24\MarkdownTable\Column;
use sabat24\MarkdownTable\Table;
use PHPUnit\Framework\TestCase;

class TableTest extends TestCase
{
    public function testSimpleTable(): void
    {
        $t = new Table();
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

    public function testTableWithAutoHeaders(): void
    {
        $t = new Table(options: ['autoHeaders' => true]);

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

    public function testTableAssociativeNonExistingWithAutoHeaders(): void
    {
        $t = new Table(options: ['autoHeaders' => true]);

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
