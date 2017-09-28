<?php
/**
 * NextCloud / ownCloud - notehierarchy
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\NoteHierarchy\Db;

class Node extends Entity
{

    protected $nodeId;
    protected $name;
    protected $txt;
    protected $level;
    protected $syntax = 'plain-text';
    protected $tags = '';
    protected $isRo = 0;
    protected $isRichtxt = 0;
    protected $hasCodebox = 0;
    protected $hasTable = 0;
    protected $hasImage = 0;
    /** @var Node */
    protected $node;

    /*public function getNode()
    {
        return $this->node;
    }

    public function setNode(Node $node)
    {
        $this->node = $node;
        return $this;
    }*/

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
            // 'syntax', 'tags', 'has_codebox', 'has_table', 'has_image',
        ];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTxt()
    {
        return $this->txt;
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
}