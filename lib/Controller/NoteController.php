<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCA\FractalNote\Service\AbstractProvider;
use OCA\FractalNote\Service\Exception\ConflictException;
use OCA\FractalNote\Service\Exception\NotFoundException;
use OCA\FractalNote\Service\Exception\WebException;
use OCA\FractalNote\Controller\AbstractController;

class NoteController extends AbstractController
{
    /**
     * @NoAdminRequired
     *
     * @param integer $mtime
     * @param mixed   $parentId
     * @param string  $title
     * @param integer $position
     *
     * @return DataResponse
     */
    public function create($mtime, $parentId, $title, $position)
    {
        if (!$this->notesProvider->isConnected()) {
            throw new NotFoundException();
        }
        if ($this->notesProvider->isExpired($parentId, $mtime)) {
            throw new ConflictException($title);
        }
        $nodeIdentifier = $this->notesProvider->createNode($parentId, $title, $position);
        return new DataResponse([$this->notesProvider->getModifyTime(), $nodeIdentifier]);
    }

    /**
     * @NoAdminRequired
     *
     * @param integer $mtime
     * @param array   $nodeData
     *
     * @return DataResponse
     */
    public function update($mtime, $nodeData)
    {
        if (!$this->notesProvider->isConnected()) {
            throw new NotFoundException();
        }
        $nodeId = array_key_exists('id', $nodeData) ? $nodeData['id'] : null;
        if (!$nodeId) {
            throw new NotFoundException(); // @todo create exception for not existing node
        }
        if ($this->notesProvider->isExpired($nodeId, $mtime)) {
            throw new ConflictException();
        }
        $this->notesProvider->updateNode(
            $nodeId,
            array_key_exists('title', $nodeData) ? $nodeData['title'] : null,
            array_key_exists('content', $nodeData) ? $nodeData['content'] : null,
            array_key_exists('newParentId', $nodeData) ? $nodeData['newParentId'] : null,
            array_key_exists('position', $nodeData) ? $nodeData['position'] : null
        );
        return new DataResponse([$this->notesProvider->getModifyTime()]);
    }

    /**
     * @NoAdminRequired
     *
     * @param integer $mtime
     * @param integer $nodeId
     *
     * @return DataResponse
     */
    public function destroy($mtime, $nodeId)
    {
        if (!$nodeId || !$this->notesProvider->isConnected()) {
            throw new NotFoundException();
        }
        if ($this->notesProvider->isExpired($nodeId, $mtime)) {
            throw new ConflictException();
        }
        $this->notesProvider->delete($nodeId);
        return new DataResponse([$this->notesProvider->getModifyTime()]);
    }

    /**
     * @NoAdminRequired
     */
    public function index()
    {
        return new DataResponse([$this->notesProvider->buildTree(), $this->notesProvider->getModifyTime()]);
    }

    /**
     * @NoAdminRequired
     *
     * @param int $nodeId
     */
    public function show($nodeId)
    {
        return new DataResponse($this->notesProvider->findNode($nodeId));
    }
}
