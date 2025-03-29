<?php

namespace sabat24\MarkdownTable;

use RuntimeException;

/**
 * Manages column attributes of a Markdown table.
 */
final class Column
{
    /**
     * @const int column alignment left
     */
    public const ALIGN_LEFT = 1;

    /**
     * @const int column alignment right
     */
    public const ALIGN_RIGHT = 2;

    /**
     * @const int column alignment center
     */
    public const ALIGN_CENTER = 3;

    /**
     * @var string column title
     */
    private string $title;

    /**
     * @var int column alignment
     */
    private int $alignment;

    /**
     * @var int max length of column
     */
    private int $length;

    /**
     * @var string PCRE regex to validate alignment constants
     */
    private string $regexAlignment;

    /**
     * Column constructor.
     * @param string $title The columns' title
     * @param int|null $alignment Optional column alignment. Default: ALIGN_LEFT
     */
    public function __construct(string $title, ?int $alignment = null)
    {
        $this->regexAlignment = '~^(' . self::ALIGN_LEFT . '|' . self::ALIGN_RIGHT . '|' . self::ALIGN_CENTER . ')$~';
        $this->length = 3;
        $this->setTitle($title);

        if ($alignment === null) {
            $alignment = self::ALIGN_LEFT;
        }
        $this->setAlignment($alignment);
    }

    /**
     * Set the columns' title.
     *
     * @param string $title The columns' title.
     * @throws \RuntimeException in case the title is no string or too short.
     */
    public function setTitle(string $title): void
    {
        $title = filter_var(
            $title,
            FILTER_SANITIZE_SPECIAL_CHARS,
            FILTER_FLAG_STRIP_BACKTICK | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH,
        );

        if ($title === '' || $title === false) {
            throw new RuntimeException('Column title is too short.');
        }

        $this->setMaxLength(mb_strlen($title));
        $this->title = $title;
    }

    /**
     * Set the columns' alignment.
     *
     * @param int $alignment The columns' alignment.
     * @throws \RuntimeException in case the given alignment is no alignment constant.
     */
    public function setAlignment(int $alignment): void
    {
        $this->alignment = (int) filter_var(
            $alignment,
            FILTER_VALIDATE_REGEXP,
            [
                'options' => [
                    'regexp' => $this->regexAlignment,
                ],
            ],
        );

        if ($this->alignment === 0) {
            throw new RuntimeException('Invalid alignment constant.');
        }
    }

    /**
     * Sets the columns' maximum length.
     *
     * @param int $length The columns' maximum length.
     * @throws \RuntimeException in case the given length is no positive integer.
     */
    public function setMaxLength(int $length): void
    {
        if ($length < 0) {
            throw new RuntimeException('Column length needs to be a non-negative integer.');
        }

        $this->length = max($this->length, $length);
    }

    /**
     * Reset a columns maximum length to a minimum of three or the title length.
     */
    public function resetMaxLength(): void
    {
        $this->length = max(3, mb_strlen($this->title));
    }

    /**
     * Create a column cell string using the given content.
     *
     * @param string $content The cells' content.
     * @return string The cells content with spaces to fill the whole cell length.
     * @throws \RuntimeException in case the given content is longer than the maximum length of this cell.
     */
    public function createCell(string $content): string
    {
        $diff = $this->length - mb_strlen($content);

        if ($diff < 0) {
            throw new RuntimeException('Content length too long.');
        }

        switch ($this->alignment) {
            case self::ALIGN_RIGHT:
                $result = sprintf('%s%s', str_repeat(' ', $diff), $content);
                break;
            case self::ALIGN_CENTER:
                $diff_left = intval(floor($diff / 2));
                $result = sprintf(
                    '%s%s%s',
                    str_repeat(' ', $diff_left),
                    $content,
                    str_repeat(' ', $diff - $diff_left),
                );
                break;
            default:
                $result = sprintf('%s%s', $content, str_repeat(' ', $diff));
                break;
        }

        unset($diff, $diff_left);

        return $result;
    }

    /**
     * Creates the column header string.
     *
     * @return string The column header string.
     */
    public function createHeader(): string
    {
        return $this->createCell($this->title);
    }

    /**
     * Creates the column header separator string.
     *
     * @return string The column header separator string.
     */
    public function createHeaderSeparator(): string
    {
        return match ($this->alignment) {
            self::ALIGN_RIGHT => sprintf(
                '%s:',
                str_repeat('-', $this->length - 1),
            ),
            self::ALIGN_CENTER => sprintf(
                ':%s:',
                str_repeat('-', $this->length - 2),
            ),
            default => str_repeat('-', $this->length),
        };
    }

    /**
     * Set the columns' alignment using a string.
     * Accepts 'l'/'left', 'r'/'right', 'c'/'center'
     *
     * @param string|null $alignment The alignment as a string
     * @return $this
     */
    public function setAlignmentFromString(?string $alignment): Column
    {
        if (is_null($alignment) || $alignment === '') {
            $this->alignment = self::ALIGN_LEFT;

            return $this;
        }

        // convert string to lowercase and get first character
        $char = strtolower(substr($alignment, 0, 1));

        $this->alignment = match ($char) {
            'r' => self::ALIGN_RIGHT,
            'c' => self::ALIGN_CENTER,
            default => self::ALIGN_LEFT,
        };

        return $this;
    }
}
