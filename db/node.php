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
 * Class Node
 *
 * @method string getTxt()
 * @method string getName()
 * @method integer getLevel()
 * @method void setLevel(integer $level)
 * @method void setName(string $title)
 * @method void setTxt(string $content)
 * @method void setSyntax(string $syntax)
 * @method void setIsRichtxt(integer $isRich)
 *
 * @package OCA\FractalNote\Db
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

    public function __construct()
    {
        parent::__construct();
        $this->addType('level', 'integer');
        $this->addType('isRo', 'boolean');
        $this->addType('isRichtxt', 'boolean');
        $this->addType('hasTable', 'boolean');
        $this->addType('hasImage', 'boolean');
        $this->addType('tsCreation', 'integer');
        $this->addType('tsLastsave', 'integer');
    }

    public function getPrimaryAttribute()
    {
        return 'node_id';
    }

    public function getAttributesNames()
    {
        return [
            'node_id',
            'name',
            'txt',
            'level',
            'is_richtxt',
            'is_ro',
            'syntax',
            'tags',
            'has_codebox',
            'has_table',
            'has_image',
            'ts_creation',
            'ts_lastsave',
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
}