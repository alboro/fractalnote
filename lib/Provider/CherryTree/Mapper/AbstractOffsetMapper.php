<?php

declare(strict_types=1);

namespace OCA\FractalNote\Provider\CherryTree\Mapper;

use OCA\FractalNote\Provider\CherryTree\Entity\OffsetEntityInterface;
use OCA\FractalNote\Provider\CherryTree\Db\Mapper;
use OCA\FractalNote\Provider\CherryTree\Db\Entity as CherryEntity;
use OCA\FractalNote\Provider\CherryTree\Entity\Image;
use OCP\AppFramework\Db\Entity as NativeEntity;
use OCP\IDBConnection;

abstract class AbstractOffsetMapper extends Mapper
{
    public function __construct(IDBConnection $db, $tableName, $entityClass)
    {
        parent::__construct($db, $tableName, $entityClass);
    }

    /**
     * @param integer $nodeId
     *
     * @return Image[]|array
     */
    public function findAllByNodeId($nodeId)
    {
        $q = $this->db->getQueryBuilder()
            ->select('*')
            ->from($this->getTableName())
            ->where('node_id = ' . $this->db->quote($nodeId))
        ;
        return $this->findEntities($q);
    }

    public function delete(NativeEntity $entity): NativeEntity
    {
        if (!$entity instanceof CherryEntity || !$entity instanceof OffsetEntityInterface) {
            throw new \Exception('Not supported for ' . get_class($entity));
        }
        $q = $this->db->getQueryBuilder();
        $q->delete($this->getTableName())
            ->where($q->expr()->eq($entity->getPrimaryColumn(), $q->createNamedParameter($entity->getId())))
            ->andWhere($q->expr()->eq('offset', $q->createNamedParameter($entity->getOffset())))
        ;
        $q->execute();
        return $entity;
    }
}