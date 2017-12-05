<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Provider;

use OCA\FractalNote\Service\AbstractProvider;

class NothingProvider extends AbstractProvider
{
    public function isConnected()
    {
        return false;
    }

    public function requireSync() {}

    public function lockResource() {}

    public function unlockResource() {}

    public function getModifyTime()
    {
        return 0;
    }

    public function isExpired($nodeId, $storedExpiration)
    {
        return false;
    }

    public function buildTree()
    {
        return [];
    }

    protected function _updateNode($nodeId, $title, $content, $newParentId, $position) {}

    protected function _delete($noteId) {}

    protected function _createNode(
        $parentNodeId,
        $title,
        $position,
        $content,
        $isRich
    ) {}
}