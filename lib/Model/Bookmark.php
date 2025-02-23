<?php

namespace OCA\FractalNote\Model;

use OCA\FractalNote\Model\Node;

/**
 * Сущность Bookmark
 * Соответствует таблице "bookmark" в базе данных.
 */
class Bookmark
{
    public function __construct(
        private Node $node,  // One-to-one связь с Node
        private int $sequence
    ) {}

    // Методы доступа к свойствам

    public function node(): Node
    {
        return $this->node;
    }

    public function sequence(): int
    {
        return $this->sequence;
    }
}
