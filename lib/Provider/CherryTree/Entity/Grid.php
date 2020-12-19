<?php
/**
 * NextCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Provider\CherryTree\Entity;

use OCA\FractalNote\Provider\CherryTree\Entity\OffsetEntityInterface;
use OCA\FractalNote\Provider\CherryTree\Db\Entity;

/**
 * Class Grid
 *
 * @method integer getNodeId()
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
 * @package OCA\FractalNote\Provider\CherryTree\Db
 */
class Grid extends Entity implements OffsetEntityInterface
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

    public function getOffset(): int
    {
        return $this->offset;
    }
}