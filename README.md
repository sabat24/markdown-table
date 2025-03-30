# markdown-table

Generate a markdown (GFM) table in PHP.

## Contents

* [What is this?](#what-is-this)
* [When should I use this?](#when-should-i-use-this)
* [Install](#install)
* [Use](#use)
* [API](#api)
    * [Table Class](#table-class)
    * [Column Class](#column-class)
    * [Options](#options)
* [Compatibility](#compatibility)

## What is this?

This is a simple package that takes table data and generates a [GitHub flavored
markdown (GFM)](https://docs.github.com/en/github/writing-on-github/working-with-advanced-formatting/organizing-information-with-tables)
table in PHP.

## When should I use this?

You can use this package when you want to generate the source code of a GFM
table from PHP data structures.

## Inspiration

This is a PHP implementation inspired by the
JavaScript [wooorm/markdown-table](https://github.com/wooorm/markdown-table)
package with similar API and functionality.

I also made it compatible with [the-kbA-team/markdown-table](https://github.com/the-kbA-team/markdown-table) because I
use this package in some projects.

## Install

In PHP projects, install with [Composer](https://getcomposer.org/):

```sh
composer require sabat24/markdown-table
```

## Use

Typical usage (defaults to align left):

```php
use sabat24\MarkdownTable\Table;

$table = new Table(['Branch', 'Commit']);
echo $table->getString([
    ['main', '0123456789abcdef'],
    ['staging', 'fedcba9876543210'],
]);
```

Yields:

```markdown
| Branch  | Commit           |
|---------|------------------|
| main    | 0123456789abcdef |
| staging | fedcba9876543210 |
```

With align:

```php
use sabat24\MarkdownTable\Table;

$table = new Table(['Beep', 'No.', 'Boop']);
$table->setAlignment(['l', 'c', 'r']);
echo $table->getString([
    ['beep', '1024', 'xyz'],
    ['boop', '3388450', 'tuv'],
    ['foo', '10106', 'qrstuv'],
    ['bar', '45', 'lmno'],
]);
```

Yields:

```markdown
| Beep |   No.   |   Boop |
|:-----|:-------:|-------:|
| beep |  1024   |    xyz |
| boop | 3388450 |    tuv |
| foo  |  10106  | qrstuv |
| bar  |   45    |   lmno |
```

With automatic headers:

```php
use sabat24\MarkdownTable\Table;

$table = new Table(options: ['autoHeaders' => true]);
echo $table->getString([
    ['Col.A', 'Col.B', 'Col.C'],
    ['a', 'z', ''],
    ['b', '', ''],
    ['c', 'y', 'x'],
]);
```

Yields:

```markdown
| Col.A | Col.B | Col.C |
|-------|-------|-------|
| a     | z     |       |
| b     |       |       |
| c     | y     | x     |
```

With custom string length function:

```php
use sabat24\MarkdownTable\Table;

// Using a custom function for handling special characters like emoji properly
function stringWidth($string): int
{
// This is a simplified example - in production, you might want
// a more sophisticated library that handles all Unicode properties
    $pattern = '/[\p{East_Asian_Width=F}\p{East_Asian_Width=W}]/u';
    $wide = preg_match_all($pattern, $string, $matches);

    return mb_strlen($string) + $wide;
}

$table = new Table(['Alpha', 'Bravo']);
$table->setStringLengthFunction('stringWidth');
echo $table->getString([
    ['中文', 'Charlie'],
    ['👩‍❤️‍👩', 'Delta'],
]);
```

# API

### Table Class

#### `__construct(array $columnNames = [], array $options = [], bool $useNamesAsPositions = false)`

Creates a new table with:
- `$columnNames`: Optional array of column names
- `$options`: Optional configuration options
- `$useNamesAsPositions`: When true, uses column names as position identifiers instead of array keys (default: false)

#### `addColumn(int|string $pos, Column $column): Table`

Adds a column to the table at the specified position.

#### `getColumn(int|string $pos): Column`

Retrieves a column at the specified position.

#### `clearColumns(): Table`

Removes all columns from the table.

#### `hasColumns(): bool`

Checks if the table has any columns defined.

#### `dropColumn(int|string $pos): Table`

Removes a column at the specified position.

#### `setStringLengthFunction(callable $callback): Table`

Sets a custom function to determine the visual length of strings, useful for handling CJK characters and emoji.

#### `setAlignment(array|string $align): Table`

Sets alignment for columns. Accepts:
- Single string for all columns: 'l'/'left', 'r'/'right', 'c'/'center'
- Array of alignments for individual columns

#### `setOptions(array $options): Table`

Sets configuration options for the table.

#### `getOptions(): array`

Gets current configuration options.

#### `getString(array $rows): string`

Generates a Markdown table string from the given rows.

### Column Class

#### `__construct(string $title, ?int $alignment = null)`

Creates a new column with the specified title and optional alignment.

#### `setAlignmentFromString(?string $alignment): Column`

Sets the column alignment using a string:
- 'l' or 'left' for left alignment
- 'r' or 'right' for right alignment
- 'c' or 'center' for center alignment

### Options

The following options can be passed to the `Table` constructor or `setOptions()` method:

##### `alignDelimiters` (bool, default: `true`)

Whether to align the delimiters. When `true`, they are aligned; when `false`, they are staggered.

##### `padding` (bool, default: `true`)

Whether to add a space of padding between delimiters and cells.

##### `delimiterStart` (bool, default: `true`)

Whether to begin each row with the delimiter.

##### `delimiterEnd` (bool, default: `true`)

Whether to end each row with the delimiter.

##### `autoHeaders` (bool, default: `false`)

Whether to use the first row of data as headers.

##### `headerSeparatorPadding` (bool, default: `false`)

Whether to add padding spaces in the header separator row.

## Compatibility

This package requires PHP 8.2 or higher.
