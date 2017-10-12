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

/**
 * Class Relation
 *
 * @method integer getNodeId()
 * @method integer getFatherId()
 * @method integer getSequence()
 * @method void setFatherId(integer $parentId)
 * @method void setNodeId(integer $nodeId)
 * @method void setSequence(integer $position)
 *
 * @package OCA\FractalNote\Db
 */
class Relation extends Entity implements JsonSerializable
{

    protected $nodeId;
    protected $fatherId;
    protected $sequence;
    /**
     *
     * @var Node
     */
    private $node;

    /**
     *
     * @var Relation[]
     */
    private $childRelations = [];

    public function getNode()
    {
        return $this->node;
    }

    public function setNode(Node $node)
    {
        $this->node = $node;

        $this->setNodeId($node->getId());

        return $this;
    }

    public function getChildRelations()
    {
        return $this->childRelations;
    }

    public function addChild(Relation $child)
    {
        $this->childRelations[] = $child;

        return $this;
    }

    public function getPrimaryPropertyName()
    {
        return 'nodeId';
    }

    public function getPropertiesConfig()
    {
        return [
            'nodeId' => [
                'type' => static::INT,
            ],
            'fatherId' => [
                'type' => static::INT,
            ],
            'sequence' => [
                'type' => static::INT,
            ],
        ];
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
