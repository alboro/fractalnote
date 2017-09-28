<?php
/**
 * NextCloud / ownCloud - notehierarchy
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\NoteHierarchy\Db;

use OCP\IDBConnection;

class RelationMapper extends Mapper
{
    const RELATED_NODE = '\OCA\NoteHierarchy\Db\Node';

    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'children', '\OCA\NoteHierarchy\Db\Relation');
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

    public function find($id)
    {
//        $sql = 'SELECT * FROM node WHERE node_id=? LIMIT 1';
//        return $this->findEntity($sql, [$id]);
    }

    public function findAll()
    {
//        $sql = 'SELECT * FROM node LIMIT 1000';
//        return $this->findEntities($sql);
    }

}
