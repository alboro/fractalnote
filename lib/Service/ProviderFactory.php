<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Service;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OC\Files\Filesystem;
use OCA\FractalNote\Provider\Folder\FolderStructure;
use OCA\FractalNote\Service\Exception\NotFoundException;
use OCA\FractalNote\Service\Exception\WebException;
use OCA\FractalNote\Provider\Nothing;
use OCA\FractalNote\Provider\CherryTree\CherryTreeStructure;

class ProviderFactory
{
    const REQUEST_KEY_CHERRYTREE = 'f';
    const REQUEST_KEY_FOLDER     = 'folder';

    /**
     * @param IRequest $request
     *
     * @return \OCA\FractalNote\Service\NotesStructure
     */
    public function createProviderByRequest(IRequest $request)
    {
        $paramKeys = array_keys($request->getParams());
        foreach ($this->supportedProviders() as $possibleProvider) {
            if (in_array($possibleProvider, $paramKeys, true)) {
                return $this->createProvider($possibleProvider, $request->getParam($possibleProvider));
            }
        }
        return $this->createDefaultProvider();
    }

    public function createDefaultProvider()
    {
        return new Nothing();
    }

    private function supportedProviders()
    {
        return [
            self::REQUEST_KEY_CHERRYTREE,
            self::REQUEST_KEY_FOLDER,
        ];
    }

    /**
     * @param $providerKey
     * @param $filesystemPathToStructure
     *
     * @return \OCA\FractalNote\Service\NotesStructure
     */
    private function createProvider($providerKey, $filesystemPathToStructure)
    {
        switch ($providerKey) {
            case self::REQUEST_KEY_CHERRYTREE:
                $instance = new CherryTreeStructure(Filesystem::getView(), $filesystemPathToStructure);
                break;
            case self::REQUEST_KEY_FOLDER:
                $instance = new FolderStructure(Filesystem::getView(), $filesystemPathToStructure);
                break;
        }
        return $instance;
    }
}