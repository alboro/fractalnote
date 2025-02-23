<?php

namespace OCA\FractalNote\Model;

use OCA\FractalNote\Service\Exception\NoChangesException;
use JsonSerializable;

/**
 * Сущность Children
 * Соответствует таблице "children" в базе данных.
 */
class Relation
    // @todo: remove
    implements JsonSerializable
{
    private ?Node $parent;

    // @todo: remove
    private array $relations = [];
    public function __construct(
        private Node $node, // One-to-one связь с Node по nodeId
        private int $parentId,
        private int  $sequence
    ) {}

    // Методы доступа к свойствам
    public function node(): Node
    {
        return $this->node;
    }

    public function parent(): ?Node
    {
        return $this->parent;
    }

    public function sequence(): int
    {
        return $this->sequence;
    }

    public function move(?Node $newParent, int $sequence): void
    {
        if (!$newParent === $this->parent && $sequence === $this->sequence) {
            throw new NoChangesException();
        }

        $this->parent = $newParent;
        null !== $sequence && $this->sequence = $sequence;
    }

    // @todo: remove
    public function jsonSerialize()
    {
        return [
            'id'       => $this->node()->nodeId(),
            'type'     => $this->node()->iconType(),
            'text'     => $this->node()->name(),
            'data'     => [
                'content'    => $this->node()->content(),
                'isEditable' => $this->node()->isEditable(),
                'isReadonly' => $this->node()->isReadOnly(),
                'isRich'     => $this->node()->isRich(),
            ],
            'children' => $this->children(),
        ];
    }

    // @todo: remove
    public function children()
    {
        return $this->relations;
    }

    // @todo: remove
    public function addChild(self $child)
    {
        $this->relations[] = $child;

        return $this;
    }
}
