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

class RelationController extends AbstractController
{
    /**
     * @NoAdminRequired
     *
     * @param integer $mtime
     * @param integer $nodeId
     * @param integer $newParentId
     */
    public function update($mtime, $nodeId, $newParentId)
    {
        return $this->handleWebErrors(function () use ($mtime, $nodeId, $newParentId) {
            $nodeId = (int)$nodeId;
            $newParentId = (int)$newParentId;
            if (!$newParentId || !$nodeId || !$this->connector->isConnected()) {
                throw new NotFoundException();
            }
            if ($this->connector->getModifyTime() !== $mtime) {
                $title = $this->notesStructure->findNode($nodeId)->getTxt();
                throw new ConflictException($title);
            }
            $this->notesStructure->move($nodeId, $newParentId);
            return [$this->connector->getModifyTime()];
        });
    }
}
