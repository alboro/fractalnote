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

use OCA\FractalNote\Provider\CherryTree\Entity\Node;
use OCP\AppFramework\Db\Entity as NativeEntity;
use OCP\IDBConnection;
use OCA\FractalNote\Provider\CherryTree\Db\Mapper;

class NodeMapper extends Mapper
{
    /**
     * NodeMapper constructor.
     *
     * @param IDBConnection $db
     */
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'node', Node::class);
    }

    /**
     * @param NativeEntity $entity
     *
     * @return NativeEntity
     * @throws \Exception
     */
    public function update(NativeEntity $entity): NativeEntity
    {
        if (!$entity instanceof Node) {
            throw new \Exception('Not supported for ' . get_class($entity));
        }
        $entity->getUpdatedFields() && $entity->setTsLastsave(time());
        return parent::update($entity);
    }
}
