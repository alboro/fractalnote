<?php

namespace OCA\FractalNote\Model;

/**
 * Сущность Image
 * Соответствует таблице "image" в базе данных.
 */
class Image
{
    public function __construct(
        private Node $node,  // Many-to-one связь с Node
        private int $offset,
        private string $justification,
        private string $anchor,
        private string $png,
        private string $filename,
        private string $link,
        private int $time
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

    public function anchor(): string
    {
        return $this->anchor;
    }

    public function png(): string
    {
        return $this->png;
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function link(): string
    {
        return $this->link;
    }

    public function time(): int
    {
        return $this->time;
    }
}
