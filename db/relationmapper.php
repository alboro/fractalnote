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
     * @param $nodeId
     *
     * @return Relation
     */
    public function find($nodeId)
    {
        $sql = 'SELECT * FROM children WHERE node_id=? LIMIT 1';
        return $this->findEntity($sql, [$nodeId]);
    }

    /**
     * @param integer $nodeId
     *
     * @return Relation[]|array
     * @todo: remove
     */
    public function findChildRelations($nodeId)
    {
        $sql = 'SELECT * FROM children WHERE father_id = ?';
        return $this->findEntity($sql, [$nodeId]);
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
        if (!$relation instanceof Relation) {
            throw new NotFoundException();
        }
        return 1 + $this->calculateLevelByParentId($relation->getFatherId());
    }
}
