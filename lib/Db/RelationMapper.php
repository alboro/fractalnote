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

use OCA\FractalNote\Service\NotFoundException;
use OCP\IDBConnection;

class RelationMapper extends Mapper
{
    const RELATED_NODE = '\OCA\FractalNote\Db\Node';

    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'children', '\OCA\FractalNote\Db\Relation');
    }

    public function relatedEntityMapping(Entity $entity)
    {
        return [
            // relatedEntityClass => $baseEntityMethod
            self::RELATED_NODE => 'setNode',
        ];
    }

    public function findChildrenWithNodes()
    {
        $sql = 'SELECT * FROM children c '
            . 'JOIN node n ON n.node_id = c.node_id '
            . 'ORDER BY n.level, c.sequence';

        return $this->findOneToOneEntities([self::RELATED_NODE], $sql);
    }

    /**
     * @param integer $nodeId
     *
     * @return Relation[]|array
     */
    public function findChildRelations($nodeId)
    {
        $sql = 'SELECT * FROM children WHERE father_id = ?';
        return $this->findEntities($sql, [$nodeId]);
    }

    /**
     * @param integer $nodeId
     *
     * @return Relation[]|array
     */
    public function findChildRelationsWithNodes($nodeId)
    {
        $sql = 'SELECT * FROM children c JOIN node n ON n.node_id = c.node_id';
        $sql.= ' WHERE c.father_id = ' . $this->db->quote($nodeId);
        return $this->findOneToOneEntities([self::RELATED_NODE], $sql);
    }

    /**
     * @param integer $parentId
     *
     * @return integer
     */
    public function calculateLevelByParentId($parentId)
    {
        if ($parentId === 0) {
            return 0;
        }
        $relation = $this->find($parentId);
        return 1 + $this->calculateLevelByParentId($relation->getFatherId());
    }

    /**
     * @return Relation[]|array
     */
    public function buildTree()
    {
        $shuffledChildren = $this->findChildrenWithNodes();
        $children = [];
        foreach ($shuffledChildren as $k => $child) {
            /* @var $child Relation */
            $children[$child->getNodeId()] = $child;
        }
        foreach ($children as $nodeId => $child) {
            /* @var $child Relation */
            $fatherId = $child->getFatherId();
            if ($fatherId && isset($children[$fatherId])) {
                $father = $children[$fatherId];
                /* @var $father Relation */
                $father->addChild($child);
            }
        }
        $children = array_filter($children, function (Relation $v) {
            return !$v->getFatherId();
        });

        return array_values($children);
    }
}
