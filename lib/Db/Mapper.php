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

use OCP\AppFramework\Db\Mapper as NativeMapper;
use OCP\AppFramework\Db\Entity as NativeEntity;

abstract class Mapper extends NativeMapper
{
    
    public function relatedEntityMapping(Entity $entity)
    {
        return [];
    }

    /**
     * @param integer $id
     *
     * @return Entity
     */
    public function find($id)
    {
        $tmpEntity = new $this->entityClass; /* @var $tmpEntity Entity */
        if (!$tmpEntity instanceof Entity) {
            throw new \Exception('Find method not implemented for ' . self::class . 'entity');
        }
        $sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `' . $tmpEntity->getPrimaryColumn() . '`=? LIMIT 1';
        return $this->findEntity($sql, [$id]);
    }

    /**
     * Creates an entity from a row. Automatically determines the entity class
     * from the current mapper name (MyEntityMapper -> MyEntity)
     * @param array  $row the row which should be converted to an entity
     * @param string $entityClassName
     * @return Entity the entity
     */
    protected function mapRowToEntity($row, $entityClassName = null)
    {
        return call_user_func(
            ($entityClassName ? : $this->entityClass) . '::fromRow',
            $row
        );
    }

    /**
     * Runs a sql query and returns an array of entities
     * @return array all fetched entities
     */
    protected function findOneToOneEntities(
        array $relatedEntityClassNames,
        $sql,
        array $params = [],
        $limit = null,
        $offset = null
    )
    {
        $stmt = $this->execute($sql, $params, $limit, $offset);
        $entities = [];

        while ($row = $stmt->fetch()) {
            $baseEntity = $this->mapRowToEntity($row);
            // search for relation entites in the result row
            if ($baseEntity instanceof Entity
            && ($relEntityMapping = $this->relatedEntityMapping($baseEntity))) {
                if ($relatedEntityClassNames) {
                    $relEntityMapping = array_intersect_key(
                        $relEntityMapping,
                        array_flip($relatedEntityClassNames)
                    );
                }
                foreach ($relEntityMapping
                as $relatedEntityClass => $baseEntityMethod) {
                    $relatedEntity = $this->mapRowToEntity($row, $relatedEntityClass);
                    $baseEntity->$baseEntityMethod($relatedEntity);
                }
            }
            $entities[] = $baseEntity;
        }

        $stmt->closeCursor();

        return $entities;
    }

    /**
     * {Inheritdoc}
     */
    public function delete(NativeEntity $entity){
        if (!$entity instanceof Entity) {
            return parent::delete($entity);
        }
        $sql = 'DELETE FROM `' . $this->getTableName() . '`'
            . ' WHERE `' . $entity->getPrimaryColumn() . '` = ?';
        $stmt = $this->execute($sql, [$entity->getId()]);
        $stmt->closeCursor();
        return $entity;
    }

    /**
     * {Inheritdoc}
     */
    public function update(NativeEntity $entity){
        if (!$entity instanceof Entity) {
            return parent::update($entity);
        }
        // if entity wasn't changed it makes no sense to run a db query
        $properties = $entity->getUpdatedFields();
        if(count($properties) === 0) {
            return $entity;
        }

        // entity needs an id
        $id = $entity->getId();
        if($id === null){
            throw new \InvalidArgumentException(
                'Entity which should be updated has no id');
        }

        // get updated fields to save, fields have to be set using a setter to
        // be saved
        // do not update the id field
        unset($properties[$entity->getPrimaryColumn()]);

        $columns = '';
        $params = [];

        // build the fields
        $i = 0;
        foreach($properties as $property => $updated) {

            $column = $entity->propertyToColumn($property);
            $getter = 'get' . ucfirst($property);

            $columns .= '`' . $column . '` = ?';

            // only append colon if there are more entries
            if($i < count($properties)-1){
                $columns .= ',';
            }

            $params[] = $entity->$getter();
            $i++;
        }

        $sql = 'UPDATE `' . $this->getTableName() . '` SET ' .
            $columns . ' WHERE `' . $entity->getPrimaryColumn() . '` = ?';
        $params[] = $id;

        $stmt = $this->execute($sql, $params);
        $stmt->closeCursor();

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function insert(NativeEntity $entity)
    {
        if (!$entity instanceof Entity) {
            return parent::insert($entity);
        }
        $id = $entity->getId();
        $result = parent::insert($entity);
        $entity->setId($id);
        return $result;
    }

    /**
     * @param $propertyName
     *
     * @return integer
     */
    public function calculateNextIncrementValue($propertyName = null)
    {
        $tmpEntity = new $this->entityClass; /* @var $tmpEntity Entity */
        $columnName = $propertyName ? $tmpEntity->propertyToColumn($propertyName) : $tmpEntity->getPrimaryColumn();
        $sql = 'select ' . $columnName . ' from `' . $this->getTableName() . '`'
            . ' order by ' . $columnName . ' desc limit 1';
        $row = $this->findOneQuery($sql);
        return isset($row[$columnName]) ? 1 + (int)$row[$columnName] : 1;
    }
}
