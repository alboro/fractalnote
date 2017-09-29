<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Db;

use JsonSerializable;

class Relation extends Entity implements JsonSerializable
{

    protected $nodeId;
    protected $fatherId;
    protected $sequence;
    /**
     *
     * @var Node
     */
    protected $node;

    /**
     *
     * @var Relation[]
     */
    protected $childRelations = [];

    public function getNode()
    {
        return $this->node;
    }

    public function setNode(Node $node)
    {
        $this->node = $node;

        return $this;
    }

    public function getChildRelations()
    {
        return $this->childRelations;
    }

    public function setChildRelations(array $childRelations)
    {
        $this->childRelations = $childRelations;

        return $this;
    }

    public function addChild(Relation $child)
    {
        $this->childRelations[] = $child;

        return $this;
    }

    public function getPrimaryAttribute()
    {
        return 'node_id';
    }

    public function getAttributesNames()
    {
        return ['node_id', 'father_id', 'sequence'];
    }

    public function getNodeId()
    {
        return $this->nodeId;
    }

    public function getFatherId()
    {
        return $this->fatherId;
    }

    public function getSequence()
    {
        return $this->sequence;
    }

    public function jsonSerialize()
    {
        switch (true) {
            case $this->getNode()->isRich():
                $iconType = 'rich';
                break;
            case $this->getNode()->isReadOnly():
                $iconType = 'readonly';
                break;
            default:
                $iconType = 'txt';
                break;
        }
        $content = $this->getNode()->getTxt();
        return [
            'id'       => $this->nodeId,
            'type'     => $iconType,
            'text'     => $this->getNode()->getName(),
            'data'     => [
                'content'    => $this->getNode()->isRich() ? html_entity_decode(strip_tags($content)) : $content,
                'isEditable' => $this->getNode()->isEditable(),
                'isReadonly' => $this->getNode()->isReadOnly(),
                'isRich'     => $this->getNode()->isRich(),
            ],
            'children' => $this->getChildRelations(),
        ];
    }

}
