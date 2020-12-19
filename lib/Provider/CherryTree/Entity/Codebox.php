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
 * Class Codebox
 *
 * @method string getJustification()
 * @method string getTxt()
 * @method string getSyntax()
 * @method integer getWidth()
 * @method integer getHeight()
 * @method integer getIsWidthPix()
 * @method integer getDoHighlBra()
 * @method integer getDoShowLinenum()
 * @method void setOffset(integer $offset)
 * @method void setJustification(string $value)
 * @method void setTxt(string $value)
 * @method void setSyntax(string $value)
 * @method void setWidth(integer $value)
 * @method void setHeight(integer $value)
 * @method void setIsWidthPix(integer $value)
 * @method void setDoHighlBra(integer $value)
 * @method void setDoShowLinenum(integer $value)
 *
 * @package OCA\FractalNote\Provider\CherryTree\Db
 */
class Codebox extends Entity implements OffsetEntityInterface
{
    protected $nodeId;
    protected $offset;
    protected $justification;
    protected $txt;
    protected $syntax;
    protected $width;
    protected $height;
    protected $isWidthPix;
    protected $doHighlBra;
    protected $doShowLinenum;

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
            'syntax' => [
                'type' => static::STR,
            ],
            'width' => [
                'type' => static::INT,
            ],
            'height' => [
                'type' => static::INT,
            ],
            'isWidthPix' => [
                'type' => static::INT,
            ],
            'doHighlBra' => [
                'type' => static::INT,
            ],
            'doShowLinenum' => [
                'type' => static::INT,
            ],
        ];
    }

    public function getOffset(): int
    {
        return $this->offset;
    }
}