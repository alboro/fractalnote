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

use OCA\FractalNote\Provider\CherryTree\CherryTreeProvider;
use OCP\IRequest;
use OCP\AppFramework\Controller as BaseController;
use OCA\FractalNote\Service\ProviderFactory;

abstract class AbstractController extends BaseController
{
    protected ?CherryTreeProvider $notesProvider;

    public function __construct($AppName, IRequest $request, string $userId, ProviderFactory $providerFactory)
    {
        // die($AppName); // todo
        parent::__construct($AppName, $request);
        if ($userId) {
            $this->notesProvider = $providerFactory->createProviderByRequest($request);
        }
    }
}