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

use OCA\FractalNote\Provider\Folder\FolderStructure;
use OCA\FractalNote\Service\Exception\NotFoundException;
use OCA\FractalNote\Service\Exception\WebException;
use \OCP\IDBConnection;
use \OCP\IRequest;
use \OC\Files\Filesystem;
use \OCA\FractalNote\Provider\CherryTree\CherryTreeStructure;

class ProviderFactory
{
    const REQUEST_KEY_CHERRYTREE = 'f';
    const REQUEST_KEY_FOLDER = 'folder';

    public function supportedProviders()
    {
        return [
            self::REQUEST_KEY_CHERRYTREE,
            self::REQUEST_KEY_FOLDER,
        ];
    }

    /**
     * @param IRequest $request
     *
     * @return string
     * @throws NotFoundException
     */
    public function getProviderKeyByRequest(IRequest $request)
    {
        $paramKeys = array_keys($request->getParams());
        foreach ($this->supportedProviders() as $possibleProvider) {
            if (in_array($possibleProvider, $paramKeys, true)) {
                return $possibleProvider;
            }
        }
        throw new NotFoundException();
    }

    /**
     * @param $providerKey
     * @param $filesystemPathToStructure
     *
     * @return \OCA\FractalNote\Service\NotesStructure
     * @throws WebException
     */
    public function createProviderInstance($providerKey, $filesystemPathToStructure)
    {
        switch ($providerKey) {
            case self::REQUEST_KEY_CHERRYTREE:
                $instance = new CherryTreeStructure(Filesystem::getView(), $filesystemPathToStructure);
                break;
            case self::REQUEST_KEY_FOLDER:
                $instance = new FolderStructure(Filesystem::getView(), $filesystemPathToStructure);
                break;
            default:
                throw new WebException(sprintf('Unknown key %s', $providerKey));
                break;
        }
        return $instance;
    }
}