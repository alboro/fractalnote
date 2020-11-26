<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Provider\CherryTree\Mapper;

use OCP\IDBConnection;
use OCA\FractalNote\Provider\CherryTree\Db\Mapper;
use OCA\FractalNote\Provider\CherryTree\Db\Entity;
use OCA\FractalNote\Provider\CherryTree\Entity\Node;
use OCA\FractalNote\Provider\CherryTree\Entity\Relation;

class RelationMapper extends Mapper
{
    /**
     * RelationMapper constructor.
     *
     * @param IDBConnection $db
     */
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'children', Relation::class);
    }

    /**
     * @return array
     */
    public function relatedEntityMapping(Entity $entity): array
    {
        return [
            // relatedEntityClass => $baseEntityMethod
            Node::class => 'setNode',
        ];
    }

    /**
     * @return Relation[]|array
     */
    public function findChildrenWithNodes()
    {
        $q = $this->db->getQueryBuilder()
            ->select('*')
            ->from($this->getTableName(), 'c')
            ->join('c', 'node', 'n', 'n.node_id = c.node_id')
            ->orderBy('n.level', 'ASC')
            ->addOrderBy('c.sequence', 'ASC')
        ;
        return $this->findOneToOneEntities([Node::class], $q);
    }

    /**
     * @return Relation[]
     */
    public function findChildRelations(int $nodeId): array
    {
        return $this->findEntities(
            $this->db->getQueryBuilder()
                ->select('*')
                ->from($this->getTableName())
                ->where('father_id = ' . $this->db->quote($nodeId))
        );
    }

    public function countChildRelations(int $nodeId): int
    {
        $q = $this->db->getQueryBuilder()
            ->select('count(*) as count')
            ->from($this->getTableName())
            ->where('father_id = ' . $this->db->quote($nodeId))
        ;
        $row = $q->execute()->fetch();
        return isset($row['count']) ? (int) $row['count'] : 0;
    }

    /**
     * @param integer $nodeId
     */
    public function findChildRelationsWithNodes($nodeId): array
    {
        $q = $this->db->getQueryBuilder()
            ->select('*')
            ->from($this->getTableName(), 'c')
            ->join('c', 'node', 'n', 'n.node_id = c.node_id')
            ->where('c.father_id = ' . $this->db->quote($nodeId))
        ;
        return $this->findOneToOneEntities([Node::class], $q);
    }

    public function calculateLevelByParentId(int $parentId): int
    {
        if ($parentId === 0) {
            return 0;
        }
        $relation = $this->find($parentId);
        return 1 + $this->calculateLevelByParentId($relation->getFatherId());
    }

    /**
     * @return Relation[]
     */
    public function buildTree(): array
    {
        $shuffledChildren = $this->findChildrenWithNodes();
        $children = [];
        foreach ($shuffledChildren as $child) {
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
