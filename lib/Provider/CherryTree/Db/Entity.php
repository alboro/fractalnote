<?php
/**
 * NextCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Provider\CherryTree\Db;

use OCP\AppFramework\Db\Entity as NativeEntity;

abstract class Entity extends NativeEntity
{
    const INT = 'integer';
    const BOOL = 'boolean';
    const STR = 'string';

    /**
     * @return mixed
     */
    abstract public function getPrimaryPropertyName();

    /**
     * @return mixed
     */
    abstract public function getPropertiesConfig();

    /**
     * Entity constructor.
     */
    public function __construct()
    {
        foreach ($this->getPropertiesConfig() as $property => $config) {
            $this->addType($property, $config['type']);
        }
    }

    /**
     * @param integer $id
     * @return void
     */
    public function setId($id)
    {
        $this->setter($this->getPrimaryPropertyName(), [$id]);
    }

    public function getId()
    {
        return $this->getter($this->getPrimaryPropertyName());
    }

    /**
     * @return string
     */
    public function getPrimaryColumn()
    {
        return $this->propertyToColumn($this->getPrimaryPropertyName());
    }

    /**
     * @param array $mayBeSeveralEntitiesRow
     *
     * @return static
     * @throws \Exception
     */
    public static function fromRow(array $mayBeSeveralEntitiesRow)
    {
        $tmpEntity = (new static);
        $propertiesConfig = $tmpEntity->getPropertiesConfig();
        $columns = [];
        foreach ($propertiesConfig as $property => $propertyConfig) {
            $columns[] = $tmpEntity->propertyToColumn($property);
        }
        $justOneEntityRow = array_intersect_key($mayBeSeveralEntitiesRow, array_flip($columns));
        if ((count($justOneEntityRow)) !== count($propertiesConfig)) {
            // @todo: make special error type and catch it
            throw new \Exception('Database row does not contain minimum required columns.');
        }
        return parent::fromRow($justOneEntityRow);
    }

}
