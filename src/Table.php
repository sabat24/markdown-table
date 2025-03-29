<?php

declare(strict_types=1);

namespace sabat24\MarkdownTable;

/**
 * Generates a Markdown table for a fixed number of columns from an array of rows.
 */
final class Table
{
    /**
     * @var Column[]
     */
    private array $columns;

    /**
     * @var int The number of columns.
     */
    private int $column_count;

    /**
     * @var array{alignDelimiters: bool, delimiterStart: bool, delimiterEnd: bool, padding: bool, autoHeaders: bool} Configuration options
     */
    private array $options = [
        'alignDelimiters' => true,
        'delimiterStart' => true,
        'delimiterEnd' => true,
        'padding' => true,
        'autoHeaders' => false,
    ];

    /**
     * @var string Markdown cell separator string.
     */
    private static string $separator = '|';

    /**
     * @var callable|null Custom string length function. mb_string will be used if null
     */
    private $stringLengthCallback = null;

    /**
     * @var array<array-key, string>|string|null Stored alignment configuration for auto-headers
     */
    private string | array | null $pendingAlignments = null;

    /**
     * Table constructor.
     * It is possible to define the columns using an array like this:
     * ['first', 'next', 'last']
     *
     * @param array<int|string, string> $keys Optional an array of column names. Default: []
     * @param array{alignDelimiters?: bool, delimiterStart?: bool, delimiterEnd?: bool, padding?: bool, autoHeaders?: bool} $options Optional configuration options. Default: []
     */
    public function __construct(array $keys = [], array $options = [])
    {
        $this->clearColumns();
        foreach ($keys as $key) {
            $this->addColumn($key, new Column($key));
        }

        $this->options = array_merge($this->options, $options);
    }

    /**
     * Remove all defined columns from this table.
     */
    public function clearColumns(): Table
    {
        $this->columns = [];
        $this->column_count = 0;

        return $this;
    }

    /**
     * Adds a column to the table.
     *
     * @param int|string $pos Unique name/id for the column position.
     */
    public function addColumn(int | string $pos, Column $column): Table
    {
        if (!array_key_exists($pos, $this->columns)) {
            $this->column_count++;
        }
        $this->columns[$pos] = $column;

        return $this;
    }

    /**
     * Return the column on the requested position.
     *
     * @param int|string $pos The column position to fetch.
     * @throws \RuntimeException in case the given position does not exist.
     */
    public function getColumn(int | string $pos): Column
    {
        if (!array_key_exists($pos, $this->columns)) {
            throw new \RuntimeException(sprintf('Column position %s does not exist!', $pos));
        }

        return $this->columns[$pos];
    }

    /**
     * Determine whether this table has columns.
     *
     * @return bool
     */
    public function hasColumns(): bool
    {
        return $this->column_count > 0;
    }

    /**
     * Remove a column from the table.
     *
     * @param int|string $pos The column position to remove.
     */
    public function dropColumn(int | string $pos): Table
    {
        if (array_key_exists($pos, $this->columns)) {
            $this->column_count--;
        }
        unset($this->columns[$pos]);

        return $this;
    }

    /**
     * Reset the length of each column to either three or the title length.
     */
    private function resetColumnLengths(): void
    {
        foreach ($this->columns as $column) {
            $column->resetMaxLength();
        }
    }

    /**
     * Set configuration options for the table
     *
     * @param array{alignDelimiters?: bool, delimiterStart?: bool, delimiterEnd?: bool, padding?: bool, autoHeaders?: bool} $options Options to set
     */
    public function setOptions(array $options): Table
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * Get current configuration options
     *
     * @return array{alignDelimiters: bool, delimiterStart: bool, delimiterEnd: bool, padding: bool, autoHeaders: bool} Current options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set a custom string length function to handle special characters properly
     *
     * @param callable $callback Function that receives a string and returns its "visual" length
     * @return $this
     */
    public function setStringLengthFunction(callable $callback): Table
    {
        $this->stringLengthCallback = $callback;

        return $this;
    }

    /**
     * Get the length of a string using the configured string length function
     *
     * @param string $value The string to measure
     * @return int The length of the string
     */
    private function getStringLength(string $value): int
    {
        \assert(is_callable($this->stringLengthCallback));
        $stringLength = call_user_func($this->stringLengthCallback, $value);

        \assert(is_int($stringLength));

        return $stringLength;
    }

    /**
     * Set alignment for columns
     *
     * @param array<int, string>|string $align Either a single alignment for all columns
     *                            or an array of alignments for each column.
     *                            Valid values: 'l'/'left', 'r'/'right', 'c'/'center'
     * @return $this
     */
    public function setAlignment(array | string $align): Table
    {
        if ($this->options['autoHeaders'] && !$this->hasColumns()) {
            // store alignments to apply after columns are created
            $this->pendingAlignments = $align;

            return $this;
        }

        if (is_array($align)) {
            // apply different alignment for each column
            $colKeys = array_keys($this->columns);
            foreach ($align as $index => $alignment) {
                if (isset($colKeys[$index])) {
                    $this->columns[$colKeys[$index]]->setAlignmentFromString($alignment);
                }
            }
        } else {
            // apply same alignment to all columns
            foreach ($this->columns as $column) {
                $column->setAlignmentFromString($align);
            }
        }

        return $this;
    }

    /**
     * Generate a Markdown table from the defined columns and their rows.
     *
     * @param array<array-key, array<array-key, bool|int|string|null>> $rows Rows of the Markdown table.
     * @return \Generator<int, string> generates a string for each row including the headers.
     * @throws \RuntimeException in case no columns are defined, or in case the rows
     *                           parameter is not an array of arrays.
     */
    public function generate(array $rows): \Generator
    {
        // auto-detect headers from first row if needed
        if ($this->options['autoHeaders'] && !empty($rows)) {
            $headerRow = reset($rows);
            if (!is_array($headerRow)) {
                throw new \RuntimeException('Rows need to be an array of arrays.');
            }
            $this->clearColumns();
            foreach ($headerRow as $key => $value) {
                $this->addColumn($key, new Column((string) $value));
            }
            // remove the header row from data rows
            array_shift($rows);

            // apply any pending alignments after columns are created
            if ($this->pendingAlignments !== null) {
                $this->setAlignment($this->pendingAlignments);
                $this->pendingAlignments = null;
            }
        }

        if (!$this->hasColumns()) {
            throw new \RuntimeException('No columns defined.');
        }

        $this->resetColumnLengths();

        /**
         * Process each row, clean each cell's string and determine the maximum
         * length of each cell based on the cleaned string. Missing cells in a row
         * get replaced by an empty string.
         */
        foreach ($rows as $id => $row) {
            if (!is_array($row)) {
                throw new \RuntimeException('Rows need to be an array of arrays.');
            }
            /**
             * Get the content of each defined column from the row.
             */
            foreach ($this->columns as $pos => $column) {
                // set an empty string for each expected column not defined in the row
                $cell = '';
                if (array_key_exists($pos, $row)) {
                    $cell = filter_var(
                        (string) $row[$pos],
                        FILTER_SANITIZE_SPECIAL_CHARS,
                        FILTER_FLAG_STRIP_BACKTICK | FILTER_FLAG_STRIP_LOW,
                    );
                    \assert(is_string($cell));
                    // use the custom string length function instead of mb_strlen
                    if ($this->stringLengthCallback !== null) {
                        $column->setMaxLength($this->getStringLength($cell));
                    } else {
                        $column->setMaxLength(mb_strlen($cell));
                    }

                }
                $row[$pos] = $cell;
            }
            $rows[$id] = $row;
        }

        /**
         * yield table header
         */
        $result = [];
        foreach ($this->columns as $column) {
            $result[] = $column->createHeader();
        }
        yield $this->formatRow($result);
        unset($result);

        /**
         * yield table header separator
         */
        $result = [];
        foreach ($this->columns as $column) {
            $result[] = $column->createHeaderSeparator();
        }
        yield $this->formatRow($result);
        unset($result);

        /**
         * yield each row
         */
        foreach ($rows as $row) {
            $result = [];
            foreach ($this->columns as $pos => $column) {
                $cell = array_key_exists($pos, $row) ? $row[$pos] : '';
                $result[] = $column->createCell((string) $cell);
            }
            yield $this->formatRow($result);
            unset($result);
        }
    }

    /**
     * Format a row according to options
     *
     * @param array<int, string> $cells Array of cell contents
     * @return string Formatted row
     */
    private function formatRow(array $cells): string
    {
        $separator = $this->options['padding'] ? sprintf(' %s ', self::$separator) : self::$separator;
        $row = implode($separator, $cells);

        // add start delimiter if enabled
        if ($this->options['delimiterStart']) {
            $row = ($this->options['padding'] ? sprintf('%s ', self::$separator) : self::$separator) . $row;
        }

        // add end delimiter if enabled
        if ($this->options['delimiterEnd']) {
            $row = $row . ($this->options['padding'] ? sprintf(' %s', self::$separator) : self::$separator);
        }

        return $row;
    }

    /**
     * Get a Markdown table as string with line breaks.
     *
     * @param array<array-key, array<array-key, bool|int|string|null>> $rows The rows to create a table from.
     * @return string The Markdown table.
     * @throws \RuntimeException in case no columns are defined, or in case the rows
     *                           parameter is not an array of arrays.
     */
    public function getString(array $rows): string
    {
        $result = '';
        foreach ($this->generate($rows) as $row) {
            $result .= sprintf('%s%s', $row, PHP_EOL);
        }

        return $result;
    }
}
