<?php

namespace OCA\FractalNote\Model;

use OCA\FractalNote\Model\Node;

/**
 * Сущность Grid
 * Соответствует таблице "grid" в базе данных.
 */
class Grid
{
    public function __construct(
        private Node $node,  // Many-to-one связь с Node
        private int $offset,
        private string $justification,
        private string $txt,
        private int $colMin,
        private int $colMax
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

    public function colMin(): int
    {
        return $this->colMin;
    }

    public function colMax(): int
    {
        return $this->colMax;
    }
}
