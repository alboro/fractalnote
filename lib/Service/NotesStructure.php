<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Service;

use Exception;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCA\FractalNote\Service\Exception\NotFoundException;

abstract class NotesStructure
{

    private $filesystemPathToStructure;

    /**
     * @param mixed $filesystemPathToStructure
     *
     * @return NotesStructure
     */
    protected function setFilesystemPathToStructure($filesystemPathToStructure)
    {
        $this->filesystemPathToStructure = $filesystemPathToStructure;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilesystemPathToStructure()
    {
        return $this->filesystemPathToStructure;
    }

    abstract public function isConnected();

    abstract public function requireSync();

    abstract public function lockResource();

    abstract public function unlockResource();

    abstract public function getModifyTime();

    /**
     * @param integer $storedExpiration
     *
     * @return mixed
     */
    abstract public function isExpired($storedExpiration);

    abstract public function buildTree();

    /**
     * @param integer $parentId
     * @param string  $title
     * @param integer $position
     * @param string  $content
     * @param integer $isRich
     *
     * @return mixed node identifier
     */
    abstract public function _createNode(
        $parentId,
        $title,
        $position,
        $content,
        $isRich
    );


    /**
     * @param mixed   $parentId
     * @param string  $title
     * @param integer $position
     * @param string  $content
     * @param integer $isRich
     *
     * @return mixed node identifier
     */
    public function createNode(
        $parentId,
        $title,
        $position,
        $content = '',
        $isRich = 0
    ) {
        try {
            $this->lockResource();

            $nodeIdentifier = $this->_createNode(
                $parentId,
                $title,
                $position,
                $content,
                $isRich
            );

            $this->unlockResource();
            $this->requireSync();
        } catch (Exception $e) {
            $this->handleException($e, true);
        }

        return $nodeIdentifier;
    }

    abstract public function _updateNode($nodeIdentifier, $title, $content, $newParentId, $position);

    public function updateNode($nodeIdentifier, $title, $content, $newParentId, $position)
    {
        try {
            $this->lockResource();

            $this->_updateNode($nodeIdentifier, $title, $content, $newParentId, $position);

            $this->unlockResource();
            $this->requireSync();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * @param $noteId
     */
    public function delete($noteId)
    {
        try {
            $this->lockResource();
            $this->_delete($noteId);
            $this->unlockResource();
            $this->requireSync();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * @param integer $noteId
     */
    abstract protected function _delete($noteId);

    protected function handleException($e, $resourceLocked = true)
    {
        if ($resourceLocked) {
            $this->unlockResource();
        }
        if ($e instanceof DoesNotExistException || $e instanceof MultipleObjectsReturnedException) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }
}
