<?php
/**
 * NextCloud / ownCloud - cherrycloud
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\CherryCloud\Db;

use OCP\AppFramework\Db\Entity as NativeEntity;

abstract class Entity extends NativeEntity
{

    abstract public function getPrimaryAttribute();

    abstract public function getAttributesNames();

    /**
     * @param integer $id
     * @return void
     */
    public function setId($id)
    {
        $property = $this->columnToProperty($this->getPrimaryAttribute());
        $this->setter($property, $id);
        // return $this;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        $property = $this->columnToProperty($this->getPrimaryAttribute());
        $id = $this->getter($property);
        /* @var integer $id */
        return $id;
    }

    public static function fromRow(array $row)
    {
        $attributes = (new static)->getAttributesNames();
        $supposedEntityColumns = array_intersect_key($row, array_flip($attributes));
        if ((count($supposedEntityColumns)) !== count($attributes)) {
            throw new \Exception(
                'database row does not contain minimum required columns'
            );
        }
        return parent::fromRow($supposedEntityColumns);
    }

}
