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
use OCA\FractalNote\Service\NotesStructure;
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
        return $this->handleWebErrors(function () use ($mtime, $parentId, $title, $position) {
            if (!$this->notesStructure->isConnected()) {
                throw new NotFoundException();
            }
            if ($this->notesStructure->isExpired($parentId, $mtime)) {
                throw new ConflictException($title);
            }
            $nodeIdentifier = $this->notesStructure->createNode($parentId, $title, $position);
            return [$this->notesStructure->getModifyTime(), $nodeIdentifier];
        });
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
        return $this->handleWebErrors(function () use ($mtime, $nodeData) {
            $nodeId = array_key_exists('id', $nodeData) ? $nodeData['id'] : null;
            if (!$nodeId || !$this->notesStructure->isConnected()) {
                throw new NotFoundException();
            }
            if ($this->notesStructure->isExpired($nodeId, $mtime)) {
                throw new ConflictException();
            }
            $this->notesStructure->updateNode(
                $nodeId,
                array_key_exists('title', $nodeData) ? $nodeData['title'] : null,
                array_key_exists('content', $nodeData) ? $nodeData['content'] : null,
                array_key_exists('newParentId', $nodeData) ? $nodeData['newParentId'] : null,
                array_key_exists('position', $nodeData) ? $nodeData['position'] : null
            );
            return [$this->notesStructure->getModifyTime()];
        });
    }

    /**
     * @param integer $mtime
     * @param integer $nodeId
     *
     * @return DataResponse
     */
    public function destroy($mtime, $nodeId)
    {
        return $this->handleWebErrors(function () use ($mtime, $nodeId) {
            if (!$nodeId || !$this->notesStructure->isConnected()) {
                throw new NotFoundException();
            }
            if ($this->notesStructure->isExpired($nodeId, $mtime)) {
                throw new ConflictException();
            }
            $this->notesStructure->delete($nodeId);
            return [$this->notesStructure->getModifyTime()];
        });
    }

    /**
     * @NoAdminRequired
     */
    public function index()
    {
        return new DataResponse([$this->notesStructure->buildTree(), $this->notesStructure->getModifyTime()]);
    }

    /**
     * @NoAdminRequired
     *
     * @param int $id
     */
    public function show($id)
    {
        return $this->handleWebErrors(function () use ($id) {
            return $this->notesStructure->findNode($id);
        });
    }
}
