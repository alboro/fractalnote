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
    /**
     * CodeboxMapper constructor.
     *
     * @param IDBConnection $db
     */
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
        $q = $this->db->getQueryBuilder()
            ->select('*')
            ->from($this->getTableName())
            ->where('node_id = ' . $this->db->quote($nodeId))
        ;
        return $this->findEntities($q);
    }

    /**
     * {Inheritdoc}
     */
    public function delete(NativeEntity $entity): NativeEntity
    {
        if (!$entity instanceof Codebox) {
            throw new \Exception('Not supported for ' . get_class($entity));
        }
        $q = $this->db->getQueryBuilder();
        $q->delete($this->tableName)
            ->where($q->expr()->eq($entity->getPrimaryColumn(), $q->createNamedParameter($entity->getId())))
            ->andWhere($q->expr()->eq('offset', $q->createNamedParameter($entity->getOffset())))
        ;
        $q->execute();
        return $entity;
    }
}
