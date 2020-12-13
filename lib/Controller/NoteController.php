<?php
/**
 * NextCloud - fractalnote
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
         return new DataResponse(
            [
                $this->notesProvider->getModifyTime(),
                $this->notesProvider->createNode(
                    (string) $parentId,
                    (string) $title,
                    (int) $position,
                    (int) $mtime
                )
            ]
        );
    }

    /**
     * @NoAdminRequired
     */
    public function update(int $mtime, array $nodeData): DataResponse
    {
        $this->notesProvider->updateNode($mtime, $nodeData);

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
     * Not in use for now
     */
    public function show($nodeId)
    {
        // return new DataResponse($this->notesProvider->findNode($nodeId));
    }
}
