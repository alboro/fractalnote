<?php

declare(strict_types=1);

namespace OCA\FractalNote\lib\Provider\CherryTree\Mapper;

use OCA\FractalNote\lib\Provider\CherryTree\Entity\OffsetEntityInterface;
use OCA\FractalNote\Provider\CherryTree\Db\Mapper;
use OCA\FractalNote\Provider\CherryTree\Db\Entity as CherryEntity;
use OCA\FractalNote\Provider\CherryTree\Entity\Image;
use OCP\AppFramework\Db\Entity as NativeEntity;
use OCP\IDBConnection;

abstract class AbstractOffsetMapper extends Mapper
{
    /**
     * ImageMapper constructor.
     *
     * @param IDBConnection $db
     */
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, $this->getTableName(), get_class($this));
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
        if (!$entity instanceof CherryEntity) {
            throw new \Exception('Not supported for ' . get_class($entity));
        }
        if (!$entity instanceof OffsetEntityInterface) {
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