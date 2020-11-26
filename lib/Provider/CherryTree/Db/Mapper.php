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

use OCA\FractalNote\Provider\CherryTree\Db\Entity as AppEntity;
use OCP\AppFramework\Db\Entity as NativeEntity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;

abstract class Mapper extends QBMapper
{
    public function relatedEntityMapping(AppEntity $entity): array
    {
        return [];
    }

    public function find(int $id): NativeEntity
    {
        $tmpEntity = new $this->entityClass;
        if (!$tmpEntity instanceof AppEntity) {
            throw new \Exception('Find method not implemented for ' . self::class . 'entity');
        }
        $q = $this->db->getQueryBuilder();
        $q->select('*')
            ->from($this->getTableName())
            ->where($tmpEntity->getPrimaryColumn() . ' = :id')
            ->setParameter(':id', $id)
            ->setMaxResults(1)
        ;
        return $this->findEntity($q);
    }

    /**
     * Creates an entity from a row. Automatically determines the entity class
     * from the current mapper name (MyEntityMapper -> MyEntity)
     * @param array  $row the row which should be converted to an entity
     * @param string $entityClassName
     * @return Entity the entity
     */
    protected function mapRowToEntity(array $row, string $entityClassName = null): NativeEntity
    {
        return \call_user_func(
            ($entityClassName ? : $this->entityClass) . '::fromRow',
            $row
        );
    }

    /**
     * Runs a sql query and returns an array of entities
     *
     * @return Entity[]
     */
    protected function findOneToOneEntities(
        array $relatedEntityClassNames,
        IQueryBuilder $queryBuilder
    ): array {
        $stmt = $queryBuilder->execute();
        $entities = [];

        while ($row = $stmt->fetch()) {
            $baseEntity = $this->mapRowToEntity($row);
            // search for relation entities in the result row
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

    public function update(NativeEntity $entity): NativeEntity
    {
        if ($entity instanceof AppEntity) {
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

            $qb = $this->db->getQueryBuilder();
            $qb->update($this->tableName);

            // build the fields
            foreach($properties as $property => $updated) {
                $column = $entity->propertyToColumn($property);
                $getter = 'get' . ucfirst($property);
                $value = $entity->$getter();

                $qb->set($column, $qb->createNamedParameter($value));
            }

            $qb->where(
                $qb->expr()->eq($entity->getPrimaryColumn(), $qb->createNamedParameter($id))
            );
            $qb->execute();

            return $entity;
        }
        return parent::update($entity);
    }

    public function insert(NativeEntity $entity): NativeEntity
    {
        if (!$entity instanceof AppEntity) {
            return parent::insert($entity);
        }
        $id = $entity->getId();
        $result = parent::insert($entity);
        $entity->setId($id);
        return $result;
    }

    public function delete(NativeEntity $entity): NativeEntity
    {
        if (!$entity instanceof AppEntity) {
            return parent::delete($entity);
        }
        $q = $this->db->getQueryBuilder();
        $q->delete($this->getTableName())
            ->where($q->expr()->eq($entity->getPrimaryColumn(), $q->createNamedParameter($entity->getId())))
        ;
        $q->execute();
        return $entity;
    }

    public function calculateNextIncrementValue($propertyName = null): int
    {
        $tmpEntity = new $this->entityClass;
        if (!$tmpEntity instanceof AppEntity) {
            throw new \Exception('calculateNextIncrementValue method not implemented for ' . get_class($tmpEntity));
        }
        $columnName = $propertyName ? $tmpEntity->propertyToColumn($propertyName) : $tmpEntity->getPrimaryColumn();

        $q = $this->db->getQueryBuilder();
        $q->select('*')
            ->from($this->getTableName())
            ->where($columnName . ' = :id')
            ->orderBy($columnName, 'DESC')
            ->setMaxResults(1)
        ;
        $row = $this->findOneQuery($q);
        return isset($row[$columnName]) ? 1 + (int)$row[$columnName] : 1;
    }
}
