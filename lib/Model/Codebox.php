<?php

namespace OCA\FractalNote\Model;

use OCA\FractalNote\Model\Node;

/**
 * Сущность Codebox
 * Соответствует таблице "codebox" в базе данных.
 */
class Codebox
{
    public function __construct(
        private Node $node,  // Many-to-one связь с Node
        private int $offset,
        private string $justification,
        private string $txt,
        private string $syntax,
        private int $width,
        private int $height,
        private int $isWidthPix,
        private int $doHighlBra,
        private int $doShowLinenum
    ) {}

    // Методы доступа к свойствам

    public function node(): Node
    {
        return $this->node;
    }

    public function offset(): int
    {
        return $this->offset;
    }

    public function justification(): string
    {
        return $this->justification;
    }

    public function txt(): string
    {
        return $this->txt;
    }

    public function syntax(): string
    {
        return $this->syntax;
    }

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
    }

    public function isWidthPix(): int
    {
        return $this->isWidthPix;
    }

    public function doHighlBra(): int
    {
        return $this->doHighlBra;
    }

    public function doShowLinenum(): int
    {
        return $this->doShowLinenum;
    }
}
