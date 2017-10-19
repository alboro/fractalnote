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

/**
 * Class Grid
 *
 * @method integer getNodeId()
 * @method integer getOffset()
 * @method string getJustification()
 * @method string getTxt()
 * @method integer getColMax()
 * @method integer getColMin()
 * @method void setNodeId(integer $value)
 * @method void setOffset(integer $offset)
 * @method void setJustification(string $value)
 * @method void setTxt(string $value)
 * @method void setColMax(integer $value)
 * @method void setColMin(integer $value)
 *
 * @package OCA\FractalNote\Db
 */
class Grid extends Entity
{
    protected $nodeId;
    protected $offset;
    protected $justification;
    protected $txt;
    protected $colMin;
    protected $colMax;

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
            'offset' => [
                'type' => static::INT,
            ],
            'justification' => [
                'type' => static::STR,
            ],
            'txt' => [
                'type' => static::STR,
            ],
            'colMin' => [
                'type' => static::INT,
            ],
            'colMax' => [
                'type' => static::INT,
            ],
        ];
    }
}