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

use OCP\AppFramework\Db\Entity as NativeEntity;

abstract class Entity extends NativeEntity
{
    const INT = 'integer';
    const BOOL = 'boolean';
    const STR = 'string';

    abstract public function getPrimaryPropertyName();

    abstract public function getPropertiesConfig();

    public function __construct()
    {
        $this->addType($this->getPrimaryPropertyName(), 'integer');
        foreach ($this->getPropertiesConfig() as $property => $config) {
            if ($property !== $this->getPrimaryPropertyName()) {
                $this->addType($property, $config['type']);
            }
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

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->getter($this->getPrimaryPropertyName());
    }

    public function getPrimaryColumn()
    {
        return $this->propertyToColumn($this->getPrimaryPropertyName());
    }

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
            throw new \Exception(
                'Database row does not contain minimum required columns.'
            );
        }
        return parent::fromRow($justOneEntityRow);
    }

}
