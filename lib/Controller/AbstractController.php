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
use OCA\FractalNote\Service\ProviderFactory;
use OCA\FractalNote\Service\Exception\WebException;
use OCA\FractalNote\Service\Exception\NotFoundException;
use OCA\FractalNote\Service\NotesStructure;
use OCP\IDBConnection;

class AbstractController extends BaseController
{
    /** @var int */
    protected $userId;
    /** @var null|NotesStructure */
    protected $notesStructure;

    /**
     * AbstractController constructor.
     *
     * @param string         $AppName
     * @param IRequest       $request
     * @param integer        $userId
     * @param NotesStructure $service
     */
    public function __construct($AppName, IRequest $request, $userId, ProviderFactory $providerFactory)
    {
        parent::__construct($AppName, $request);
        $this->userId = $userId;
        if ($this->userId) {
            $possibleProviderKey = $providerFactory->getProviderKeyByRequest($request);
            $this->notesStructure = $providerFactory->createProviderInstance(
                $possibleProviderKey,
                $request->getParam($possibleProviderKey)
            );
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