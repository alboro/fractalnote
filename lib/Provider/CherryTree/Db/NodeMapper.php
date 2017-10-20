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

use OCP\AppFramework\Db\Entity as NativeEntity;
use OCP\IDBConnection;
use OCA\FractalNote\Provider\CherryTree\Db\Node;

class NodeMapper extends Mapper
{
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'node', Node::class);
    }

    public function update(NativeEntity $entity)
    {
        if (!$entity instanceof Node) {
            throw new \Exception('Not supported for ' . get_class($entity));
        }
        $entity->getUpdatedFields() && $entity->setTsLastsave(time());
        return parent::update($entity);
    }
}
