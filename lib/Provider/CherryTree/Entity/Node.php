<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Provider\CherryTree\Entity;

use OCA\FractalNote\Provider\CherryTree\Db\Entity;

/**
 * Class Node
 *
 * @method string getTxt()
 * @method string getName()
 * @method integer getLevel()
 * @method string getSyntax()
 * @method string getTags()
 * @method boolean getIsRo()
 * @method boolean getIsRichtxt()
 * @method boolean getHasCodebox()
 * @method boolean getHasTable()
 * @method boolean getHasImage()
 * @method integer getTsCreation()
 * @method integer getTsLastsave()
 * @method void setLevel(integer $level)
 * @method void setName(string $title)
 * @method void setTxt(string $content)
 * @method void setTags(string $tags)
 * @method void setSyntax(string $syntax)
 * @method void setIsRichtxt(integer $isRich)
 * @method void setIsRo(boolean $value)
 * @method void setHasCodebox(boolean $value)
 * @method void setHasTable(boolean $value)
 * @method void setHasImage(boolean $value)
 * @method void setTsCreation(integer $timestamp)
 * @method void setTsLastsave(integer $timestamp)
 *
 * @package OCA\FractalNote\Provider\CherryTree\Db
 */
class Node extends Entity
{

    protected $nodeId;
    protected $name;
    protected $txt;
    protected $level;
    protected $syntax;
    protected $tags;
    protected $isRo;
    protected $isRichtxt;
    protected $hasCodebox;
    protected $hasTable;
    protected $hasImage;
    protected $tsCreation;
    protected $tsLastsave;
    /** @var Node */
    protected $node;

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
            'name' => [
                'type' => static::STR,
            ],
            'txt' => [
                'type' => static::STR,
            ],
            'level' => [
                'type' => static::INT,
            ],
            'isRichtxt' => [
                'type' => static::BOOL,
            ],
            'isRo' => [
                'type' => static::BOOL,
            ],
            'syntax' => [
                'type' => static::STR,
            ],
            'tags' => [
                'type' => static::STR,
            ],
            'hasCodebox' => [
                'type' => static::BOOL,
            ],
            'hasTable' => [
                'type' => static::BOOL,
            ],
            'hasImage' => [
                'type' => static::BOOL,
            ],
            'tsCreation' => [
                'type' => static::INT,
            ],
            'tsLastsave' => [
                'type' => static::INT,
            ],
        ];
    }

    public function isRich()
    {
        return $this->isRichtxt;
    }

    public function isReadOnly()
    {
        return $this->isRo;
    }

    public function isEditable()
    {
        return !$this->isReadOnly() && !$this->isRich();
    }

    public static function factory()
    {
        // default values must be set with setter, rather then with native default property values
        $note = new static();
        $note->setSyntax('plain-text');
        $note->setTsCreation(time());
        $note->setTsLastsave(time());
        $note->setLevel(0);
        $note->setIsRo(0);
        $note->setHasCodebox(0);
        $note->setHasTable(0);
        $note->setHasImage(0);
        $note->setTags('');

        return $note;
    }

    /**
     * @todo: i don't like this override
     *
     * @param array $mayBeSeveralEntitiesRow
     *
     * @return static
     */
    public static function fromRow(array $mayBeSeveralEntitiesRow)
    {
        // default values for the rows, that have been added into ctb format later and may be missing in old documents
        $mayBeSeveralEntitiesRow = array_merge(
            ['ts_lastsave' => 0, 'ts_creation' => 0],
            $mayBeSeveralEntitiesRow
        );
        return parent::fromRow($mayBeSeveralEntitiesRow);
    }


}