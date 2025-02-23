<?php
/**
 * NextCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Controller;

use OCP\AppFramework\Http\DataResponse;
use OCA\FractalNote\Service\Exception\ConflictException;
use OCA\FractalNote\Service\Exception\NotFoundException;

class NoteController extends AbstractController
{
    /**
     * @NoAdminRequired
     */
    public function create($mtime, $parentId, $title, $position): DataResponse
    {
        $nodeId = $this->notesProvider->createNode(
            (string) $parentId,
            (string) $title,
            (int) $position,
            (int) $mtime
        );
         return new DataResponse(
            [
                $this->notesProvider->getModifyTime(),
                $nodeId
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
     */
    public function destroy($mtime, $nodeId): DataResponse
    {
        if (!$nodeId) {
            throw new NotFoundException();
        }
        if ($this->notesProvider->isExpired($nodeId, $mtime)) {
            throw new ConflictException();
        }
        $this->notesProvider->delete((int) $nodeId);
        return new DataResponse([$this->notesProvider->getModifyTime()]);
    }

    /**
     * @NoAdminRequired
     */
    public function index(): DataResponse
    {
        return new DataResponse([$this->notesProvider->buildRelationTree(), $this->notesProvider->getModifyTime()]);
    }

    /**
     * Not in use for now
     */
    public function show($nodeId): void
    {
    }
}
