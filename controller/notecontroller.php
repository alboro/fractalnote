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
use OCA\FractalNote\Service\ConflictException;
use OCA\FractalNote\Service\NotFoundException;
use OCA\FractalNote\Service\WebException;
use OCA\FractalNote\Controller\AbstractController;

class NoteController extends AbstractController
{
    /**
     * @NoAdminRequired
     */
    public function index()
    {
        return new DataResponse([$this->notesStructure->buildTree(), $this->connector->getModifyTime()]);
    }

    /**
     * @NoAdminRequired
     *
     * @param int    $parentId
     * @param string $title
     * @param int    $sequence
     * @param int    $mtime
     *
     * @return DataResponse
     */
    public function create($parentId, $title, $sequence, $mtime)
    {
        return $this->handleWebErrors(function () use ($parentId, $title, $sequence, $mtime) {
            if (!$this->connector->isConnected()) {
                throw new NotFoundException();
            }
            if ($this->connector->getModifyTime() !== $mtime) {
                throw new ConflictException($title);
            }
            $relation = $this->notesStructure->create($parentId, $title, $sequence);
            return [$this->connector->getModifyTime(), $relation->getNodeId()];
        });
    }

    /**
     * @NoAdminRequired
     *
     * @param int     $id
     * @param string  $title
     * @param string  $content
     * @param integer $mtime
     */
    public function update($id, $title, $content, $mtime)
    {
        return $this->handleWebErrors(function () use ($id, $title, $content, $mtime) {
            $id = (int)$id;
            if (!$id || !$this->connector->isConnected()) {
                throw new NotFoundException();
            }
            if ($this->connector->getModifyTime() !== $mtime) {
                throw new ConflictException($title);
            }
            $this->notesStructure->update($id, $title, $content);
            return [$this->connector->getModifyTime()];
        });
    }
}
