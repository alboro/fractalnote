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
        $sql = 'DELETE FROM `' . $this->tableName . '`'
            . ' WHERE `' . $entity->getPrimaryAttribute() . '` = ?';
        $stmt = $this->execute($sql, [$entity->getId()]);
        $stmt->closeCursor();
        return $entity;
    }

    /**
     * Updates an entry in the db from an entity
     * @throws \InvalidArgumentException if entity has no id
     * @param Entity $entity the entity that should be created
     * @return Entity the saved entity with the set id
     * @since 7.0.0 - return value was added in 8.0.0
     */
    public function update(NativeEntity $entity){
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
        unset($properties[$entity->getPrimaryAttribute()]);

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

        $sql = 'UPDATE `' . $this->tableName . '` SET ' .
            $columns . ' WHERE `' . $entity->getPrimaryAttribute() . '` = ?';
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
        $id = $entity->getId();
        $result = parent::insert($entity);
        $entity->setId($id);
        return $result;
    }

    /**
     * @param $fieldName
     *
     * @return integer
     */
    public function calculateNextIncrementValue($fieldName = null)
    {
        $fieldName = $fieldName ? : (new $this->entityClass)->getPrimaryAttribute();
        $sql = 'select ' . $fieldName . ' from `' . $this->tableName . '` order by ' . $fieldName . ' desc limit 1';
        $row = $this->findOneQuery($sql);
        return isset($row[$fieldName]) ? 1 + (int)$row[$fieldName] : 1;
    }
}
