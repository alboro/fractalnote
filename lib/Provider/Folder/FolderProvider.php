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

use Exception;
use OC\Files\Filesystem;
use OCA\FractalNote\Service\Exception\WebException;
use OCA\FractalNote\Service\Exception\NoChangesException;
use OCA\FractalNote\Service\Exception\NotFoundException;
use OCA\FractalNote\Service\Exception\NotEditableException;
use OCA\FractalNote\Service\AbstractProvider;
use OCA\FractalNote\Provider\Folder\Node;
use OC\Files\View;

class FolderProvider extends AbstractProvider
{

    /** @var View */
    private $viewer;

    public function __construct(View $view, $folderPath)
    {
        $this->viewer = $view;
        $this->setFilesystemPathToStructure($folderPath);
    }

    public function isConnected()
    {
        return $this->viewer->is_dir($this->getFilesystemPathToStructure());
    }

    public function requireSync() {}

    public function lockResource()
    {
        $this->viewer->lockFile($this->getFilesystemPathToStructure(), \OCP\Lock\ILockingProvider::LOCK_SHARED, true);
    }

    public function unlockResource()
    {
        $this->viewer->unlockFile($this->getFilesystemPathToStructure(), \OCP\Lock\ILockingProvider::LOCK_SHARED, true);
    }

    public function getModifyTime()
    {
        return $this->_getModifyTime($this->buildTree());
    }

    private function _getModifyTime(array $tree)
    {
        $mtime = 0;
        foreach ($tree as $node) {
            /* @var $node Node */
            if ($node->isFolder()) {
                $tmpTime = $this->_getModifyTime($node->getChildren());
            } else {
                $tmpTime = $node->getInfo()->getMTime();
            }
            if ($tmpTime > $mtime) {
                $mtime = $tmpTime;
            }
        }
        return $mtime;
    }

    public function isExpired($nodeId, $storedExpiration)
    {
        return $storedExpiration !== $this->getModifyTime();
    }

    /**
     * @return Node[]
     */
    public function buildTree()
    {
        return $this->readFolder($this->getFilesystemPathToStructure());
    }

    /**
     * @param $folder
     *
     * @return Node[]
     */
    protected function readFolder($folder)
    {
        $tree = [];
        $data = $this->viewer->getDirectoryContent($folder);
        foreach ($data as $info) {
            /** @var $info \OC\Files\FileInfo */
            $node = new Node($info);
            if ($node->isFolder()) {
                $node->setChildren(
                    $this->readFolder(substr($info->getPath(), strlen($this->viewer->getRoot())))
                );
            } elseif ($node->isPlainText()) {
                list($storage, $internalPath) = Filesystem::resolvePath($info->getPath());
                $text = file_get_contents($storage->getLocalFile($internalPath));
                $node->setContent(mb_convert_encoding($text, 'UTF-8'));
            } else {
                continue;
            }
            $tree[] = $node;
        }
        return $tree;
    }

    protected function _updateNode($nodeId, $title, $content, $newParentId, $position)
    {
        // TODO: Implement _updateNode() method.
    }

    protected function _delete($noteId)
    {
        // TODO: Implement _delete() method.
    }

    protected function _createNode(
        string $parentNodeId,
        string $title,
        int $position,
        string $content,
        bool $isRich
    ): string {
        return '';
    }
}
