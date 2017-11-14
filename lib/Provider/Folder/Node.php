<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Provider\Folder;

use JsonSerializable;
use \OCA\FractalNote\Service\NotesStructure;
use \OC\Files\FileInfo;

/**
 * Class Node
 *
 * @package OCA\FractalNote\Provider\Folder\Fs
 */
class Node implements JsonSerializable
{

    /** @var FileInfo */
    private $fileInfo;

    /** @var string */
    private $content;

    /** @var array */
    private $children;

    public function __construct(FileInfo $info)
    {
        $this->fileInfo = $info;
    }

    public function getId()
    {
        return $this->fileInfo->getId();
    }

    public function getPath()
    {
        return $this->fileInfo->getPath();
    }

    public function getName()
    {
        return $this->fileInfo->getName();
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function isFolder()
    {
        return $this->fileInfo->getType() === 'dir';
    }

    public function isPlainText()
    {
        return $this->fileInfo->getMimePart() === 'text';
    }

    public function jsonSerialize()
    {
        switch (true) {
            case $this->isPlainText():
                $iconType = NotesStructure::TYPE_PLAINTEXT;
                break;
            default:
                $iconType = NotesStructure::TYPE_READONLY;
                break;
        }
        return [
            'id'       => $this->getId(),
            'type'     => $iconType,
            'text'     => $this->getName(),
            'data'     => [
                'content'    => $this->getContent(),
                'isEditable' => !$this->isFolder(),
                'isReadonly' => $this->isFolder(),
                'isRich'     => false,
            ],
            'children' => $this->children,
        ];
    }

    /**
     * @param Node[] $nodes
     */
    public function setChildren(array $nodes)
    {
        $this->children = $nodes;
    }

    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return FileInfo
     */
    public function getInfo()
    {
        return $this->fileInfo;
    }
}
