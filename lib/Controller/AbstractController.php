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
use OCP\AppFramework\Http;
use OCP\AppFramework\Controller as BaseController;
use OCA\FractalNote\Service\WebException;
use OCA\FractalNote\Service\NotFoundException;
use OCA\FractalNote\Service\NotesStructure;
use OCA\FractalNote\Service\Connector;

class AbstractController extends BaseController
{
    const REQUEST_KEY_FILE_PATH = 'f';

    /** @var int */
    protected $userId;
    /** @var NotesStructure */
    protected $notesStructure;
    /** @var Connector */
    protected $connector;

    /**
     * AbstractController constructor.
     *
     * @param string         $AppName
     * @param IRequest       $request
     * @param integer        $userId
     * @param NotesStructure $service
     */
    public function __construct($AppName, IRequest $request, $userId, NotesStructure $service)
    {
        parent::__construct($AppName, $request);
        $this->userId = $userId;
        $this->notesStructure = $service;
        if ($this->userId) {
            $this->connector = Connector::run(
                $request->getParam(self::REQUEST_KEY_FILE_PATH)
            );
            $this->notesStructure->setConnector($this->connector);
        }
    }

    /**
     * @param \Closure $callback
     *
     * @return DataResponse
     */
    protected function handleWebErrors(\Closure $callback)
    {
        try {
            $response = $callback();
        } catch (WebException $webError) {}

        return isset($webError) ? $webError->createResponse() : new DataResponse($response, Http::STATUS_OK);
    }
}