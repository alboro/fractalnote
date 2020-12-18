<?php
/**
 * NextCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Service;

use Exception;
use OCA\FractalNote\Service\Exception\ConflictException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCA\FractalNote\Service\Exception\NotFoundException;

abstract class AbstractProvider
{
    public const TYPE_RICH      = 'rich';
    public const TYPE_PLAINTEXT = 'txt';
    public const TYPE_READONLY  = 'readonly';

    private $filesystemPathToStructure;

    abstract public function isConnected();

    abstract public function requireSync();

    abstract public function lockResource();

    abstract public function unlockResource();

    abstract public function getModifyTime();

    /**
     * @param int|string $nodeId
     * @param integer    $storedExpiration
     *
     * @return mixed
     */
    abstract public function isExpired($nodeId, $storedExpiration);

    /**
     * @return \JsonSerializable[]|array
     */
    abstract public function buildTree(): array;

    abstract protected function _updateNode($nodeId, $title, $content, $newParentId, $position);

    /**
     * @param integer $noteId
     */
    abstract protected function _delete($noteId);

    /**
     * @param integer|string $parentNodeId
     * @param string         $title
     * @param integer        $position
     * @param string         $content
     * @param integer        $isRich
     *
     * @return mixed node identifier
     */
    abstract protected function _createNode(
        string $parentNodeId,
        string $title,
        int $position,
        string $content,
        bool $isRich
    ): string;


    /**
     * @return mixed
     */
    public function getFilesystemPathToStructure()
    {
        return $this->filesystemPathToStructure;
    }

    public function createNode(
        string $parentId,
        string $title,
        int $position,
        int $mtime,
        string $content = ''
    ): string {
        if (!$this->isConnected()) {
            throw new NotFoundException();
        }
        if ($this->isExpired($parentId, $mtime)) {
            throw new ConflictException($title);
        }

        try {
            $this->lockResource();

            $nodeIdentifier = $this->_createNode(
                $parentId,
                $title,
                $position,
                $content,
                false
            );

            $this->unlockResource();
            $this->requireSync();
        } catch (\Throwable $e) {
            $this->handleException($e);
        }

        return $nodeIdentifier;
    }

    public function updateNode(int $mtime, array $nodeData)
    {
        if (!$this->isConnected()) {
            throw new NotFoundException();
        }
        $nodeId = array_key_exists('id', $nodeData) ? $nodeData['id'] : null;
        if (!$nodeId) {
            throw new NotFoundException();
        }
        if ($this->isExpired($nodeId, $mtime)) {
            throw new ConflictException();
        }
        try {
            $this->lockResource();

            $this->_updateNode(
                $nodeId,
                $nodeData['title'] ?? null,
                $nodeData['content'] ?? null,
                $nodeData['newParentId'] ?? null,
                $nodeData['position'] ?? null
            );
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

    protected function handleException(\Throwable $e, $resourceLocked = true)
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

    /**
     * @param mixed $filesystemPathToStructure
     *
     * @return AbstractProvider
     */
    protected function setFilesystemPathToStructure($filesystemPathToStructure)
    {
        $this->filesystemPathToStructure = $filesystemPathToStructure;

        return $this;
    }
}
