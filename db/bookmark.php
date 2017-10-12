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

use JsonSerializable;

/**
 * Class Bookmark
 *
 * @method integer getNodeId()
 * @method integer getSequence()
 * @method void setNodeId(integer $nodeId)
 * @method void setSequence(integer $position)
 *
 * @package OCA\FractalNote\Db
 */
class Bookmark extends Entity
{

    protected $nodeId;
    protected $sequence;

    public function getPrimaryPropertyName()
    {
        return 'nodeId';
    }

    public function getPropertiesConfig()
    {
        return [
            'nodeId' => [
                'type' => static::INT,
            ],
            'sequence' => [
                'type' => static::INT,
            ],
        ];
    }
}