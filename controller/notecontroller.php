<?php
/**
 * NextCloud / ownCloud - cherrycloud
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\CherryCloud\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCA\CherryCloud\Service\NotesStructure;
use OCA\CherryCloud\Service\ConflictException;
use OCA\CherryCloud\Service\NotFoundException;
use OCA\CherryCloud\Service\WebException;
use OCA\CherryCloud\Controller\AbstractController;

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
     * @param int     $id
     * @param string  $title
     * @param string  $content
     * @param integer $mtime
     */
    public function update($id, $title, $content, $mtime)
    {
        return $this->handleWebErrors(function () use ($id, $title, $content, $mtime) {
            if (!$this->connector->isConnected()) {
                throw new NotFoundException();
            }
            if ($this->connector->getModifyTime() !== $mtime) {
                throw new ConflictException($title);
            }
            $isOk = $this->notesStructure->update($id, $title, $content);
            if (!$isOk) {
                throw new WebException();
            }
            $newmtime = $this->connector->getModifyTime();
            return [$newmtime];
        });
    }
}
