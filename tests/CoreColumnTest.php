<?php

declare(strict_types=1);

namespace Tests\sabat24\MarkdownTable;

use sabat24\MarkdownTable\Column;
use PHPUnit\Framework\TestCase;

class CoreColumnTest extends TestCase
{
    /**
     * Test creating a left aligned column.
     */
    public function testLeftAlignmentColumn(): void
    {
        $l = new Column('My Column');
        $this->assertEquals('My Column', $l->createHeader());
        $this->assertEquals('---------', $l->createHeaderSeparator());
        $this->assertEquals('a        ', $l->createCell('a'));
    }

    /**
     * Test creating a right aligned column.
     */
    public function testRightAlignmentColumn(): void
    {
        $r = new Column('My Column', Column::ALIGN_RIGHT);
        $this->assertEquals('My Column', $r->createHeader());
        $this->assertEquals('--------:', $r->createHeaderSeparator());
        $this->assertEquals('        a', $r->createCell('a'));
    }

    /**
     * Test creating a centered column.
     */
    public function testCenterAlignmentColumn(): void
    {
        $c = new Column('My Column');
        $c->setAlignment(Column::ALIGN_CENTER);
        $this->assertEquals('My Column', $c->createHeader());
        $this->assertEquals(':-------:', $c->createHeaderSeparator());
        $this->assertEquals('    a    ', $c->createCell('a'));
    }

    /**
     * Test that an overwriting shorter title, which is not the default use-case,
     * results in longer columns.
     */
    public function testOverwritingWithShorterTitle(): void
    {
        $a = new Column('You should not be able to read this.');
        $a->setTitle('My Column');
        $this->assertEquals('My Column                           ', $a->createHeader());
        $this->assertEquals('------------------------------------', $a->createHeaderSeparator());
        $this->assertEquals('a                                   ', $a->createCell('a'));
    }

    /**
     * Test whether a minimum length of three is maintained even if title and content
     * are shorter.
     */
    public function testDefaultLengthOfThree(): void
    {
        $l = new Column('A');
        $l->setMaxLength(mb_strlen('ab'));
        $this->assertEquals('A  ', $l->createHeader());
        $this->assertEquals('---', $l->createHeaderSeparator());
        $this->assertEquals('ab ', $l->createCell('ab'));
    }

    /**
     * Exception test in case the title is empty.
     */
    public function testShortTitle(): void
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Column title is too short.');
        new Column('`');
    }

    /**
     * Exception test in case an invalid alignment constant is used.
     */
    public function testInvalidAlignment(): void
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Invalid alignment constant.');
        new Column('My Column', 1000);
    }

    /**
     * Exception test in case the content to be rendered is unexpectedly long.
     */
    public function testUnexpectedlyLongContent(): void
    {
        $a = new Column('AAA');
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Content length too long.');
        $a->createCell('aaaa');
    }
}
