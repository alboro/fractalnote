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
use OCP\AppFramework\Controller as BaseController;
use OCA\FractalNote\Service\ProviderFactory;
use OCA\FractalNote\Service\NotesStructure;
use OCA\FractalNote\Service\Exception\NotFoundException;

class AbstractController extends BaseController
{
    /** @var null|NotesStructure */
    protected $notesStructure;

    /**
     * AbstractController constructor.
     *
     * @param string          $AppName
     * @param IRequest        $request
     * @param integer         $userId
     * @param ProviderFactory $providerFactory
     */
    public function __construct($AppName, IRequest $request, $userId, ProviderFactory $providerFactory)
    {
        parent::__construct($AppName, $request);
        if ($userId) {
            try {
                $this->notesStructure = $providerFactory->createProviderByRequest($request);
            } catch (NotFoundException $e) {
                $this->notesStructure = $providerFactory->createDefaultProvider();
            }
        }
    }
}