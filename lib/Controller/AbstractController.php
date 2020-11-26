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
use OCA\FractalNote\Service\AbstractProvider;

class AbstractController extends BaseController
{
    /** @var null|AbstractProvider */
    protected $notesProvider;

    /**
     * AbstractController constructor.
     *
     * @param string          $AppName
     * @param IRequest        $request
     * @param string          $userId
     * @param ProviderFactory $providerFactory
     */
    public function __construct($AppName, IRequest $request, $userId, ProviderFactory $providerFactory)
    {
        parent::__construct($AppName, $request);
        if ($userId) {
            $this->notesProvider = $providerFactory->createProviderByRequest($request);
        }
    }
}