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

use OCA\FractalNote\Provider\CherryTree\Entity\Image;
use OCP\AppFramework\Db\Entity as NativeEntity;
use OCP\IDBConnection;
use OCA\FractalNote\Provider\CherryTree\Db\Mapper;

class ImageMapper extends Mapper
{
    /**
     * ImageMapper constructor.
     *
     * @param IDBConnection $db
     */
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'image', Image::class);
    }

    /**
     * @param integer $nodeId
     *
     * @return Image[]|array
     */
    public function findImages($nodeId)
    {
        $q = $this->db->getQueryBuilder()
            ->select('*')
            ->from($this->getTableName())
            ->where('node_id = ' . $this->db->quote($nodeId))
        ;
        return $this->findEntities($q);
    }

    public function delete(NativeEntity $entity): NativeEntity {
        if (!$entity instanceof Image) {
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
