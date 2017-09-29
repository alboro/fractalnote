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

use OCP\IDBConnection;

class NodeMapper extends Mapper
{

    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'node', '\OCA\FractalNote\Db\Node');
    }

    /**
     * @param $id
     *
     * @return Node
     */
    public function find($id)
    {
        $sql = 'SELECT * FROM node n WHERE n.node_id=? LIMIT 1';

        return $this->findEntity($sql, [$id]);
    }

    /**
     * @return Node[]
     */
    public function findAll()
    {
        $sql = 'SELECT * FROM node n LIMIT 1000';

        return $this->findEntities($sql);
    }

}
