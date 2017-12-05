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
use OCA\FractalNote\Provider\Folder\FolderProvider;
use OCA\FractalNote\Service\Exception\NotFoundException;
use OCA\FractalNote\Service\Exception\WebException;
use OCA\FractalNote\Provider\NothingProvider;
use OCA\FractalNote\Provider\CherryTree\CherryTreeProvider;

class ProviderFactory
{
    const REQUEST_KEY_CHERRYTREE = 'f';
    const REQUEST_KEY_FOLDER     = 'folder';

    /**
     * @param IRequest $request
     *
     * @return \OCA\FractalNote\Service\AbstractProvider
     */
    public function createProviderByRequest(IRequest $request)
    {
        $paramKeys = array_keys($request->getParams());
        try {
            foreach ($this->supportedProviders() as $possibleProvider) {
                if (in_array($possibleProvider, $paramKeys, true)) {
                    return $this->createProvider($possibleProvider, $request->getParam($possibleProvider));
                }
            }
        } catch (NotFoundException $e) {
        }
        return $this->createDefaultProvider();
    }

    public function createDefaultProvider()
    {
        return new NothingProvider();
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
     * @return \OCA\FractalNote\Service\AbstractProvider
     *
     * @throws NotFoundException
     */
    private function createProvider($providerKey, $filesystemPathToStructure)
    {
        switch ($providerKey) {
            case self::REQUEST_KEY_CHERRYTREE:
                $instance = new CherryTreeProvider(Filesystem::getView(), $filesystemPathToStructure);
                break;
            case self::REQUEST_KEY_FOLDER:
                $instance = new FolderProvider(Filesystem::getView(), $filesystemPathToStructure);
                break;
        }
        return $instance;
    }
}