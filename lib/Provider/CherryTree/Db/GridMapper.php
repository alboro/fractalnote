<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Provider\CherryTree\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\Entity as NativeEntity;
use OCA\FractalNote\Provider\CherryTree\Db\Grid;

class GridMapper extends Mapper
{
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'grid', Grid::class);
    }

    /**
     * @param integer $nodeId
     *
     * @return Grid[]|array
     */
    public function findGrids($nodeId)
    {
        $sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE node_id = ?';
        return $this->findEntities($sql, [$nodeId]);
    }

    /**
     * {Inheritdoc}
     */
    public function delete(NativeEntity $entity){
        if (!$entity instanceof Grid) {
            throw new \Exception('Not supported for ' . get_class($entity));
        }
        $sql = 'DELETE FROM `' . $this->getTableName() . '`'
            . ' WHERE `' . $entity->getPrimaryColumn() . '` = ?'
            . ' AND `offset` = ?';
        $stmt = $this->execute($sql, [$entity->getId(), $entity->getOffset()]);
        $stmt->closeCursor();
        return $entity;
    }
}