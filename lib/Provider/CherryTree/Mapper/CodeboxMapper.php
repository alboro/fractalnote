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
use OCP\AppFramework\Db\Entity as NativeEntity;
use OCA\FractalNote\Provider\CherryTree\Entity\Codebox;
use OCA\FractalNote\Provider\CherryTree\Db\Mapper;

class CodeboxMapper extends Mapper
{
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'codebox', Codebox::class);
    }

    /**
     * @param integer $nodeId
     *
     * @return Codebox[]|array
     */
    public function findCodeboxes($nodeId)
    {
        $sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE node_id = ?';
        return $this->findEntities($sql, [$nodeId]);
    }

    /**
     * {Inheritdoc}
     */
    public function delete(NativeEntity $entity)
    {
        if (!$entity instanceof Codebox) {
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
