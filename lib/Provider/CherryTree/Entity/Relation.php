<?php
/**
 * NextCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Provider\CherryTree\Entity;

use JsonSerializable;
use OCA\FractalNote\Service\AbstractProvider;
use OCA\FractalNote\Provider\CherryTree\Db\Entity;

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
 * @package OCA\FractalNote\Provider\CherryTree\Db
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
        return [
            'id'       => $this->nodeId,
            'type'     => $this->getNode()->getIconType(),
            'text'     => $this->getNode()->getName(),
            'data'     => [
                'content'    => $this->getNode()->getContent(),
                'isEditable' => $this->getNode()->isEditable(),
                'isReadonly' => $this->getNode()->isReadOnly(),
                'isRich'     => $this->getNode()->isRich(),
            ],
            'children' => $this->getChildRelations(),
        ];
    }

}
