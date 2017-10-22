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

use OCA\FractalNote\Service\Exception\NotFoundException;
use OCA\FractalNote\Service\Exception\WebException;
use \OCP\IDBConnection;
use \OCP\IRequest;
use \OC\Files\Filesystem;
use \OCA\FractalNote\Service\NotesStructure;
use \OCA\FractalNote\Provider\CherryTree\CherryTreeStructure;
use \OCA\FractalNote\Provider\CherryTree\Db\SqliteConnectionFactory;

class ProviderFactory
{
    const REQUEST_KEY_CHERRYTREE = 'f';

    public function supportedProviders()
    {
        return [
            self::REQUEST_KEY_CHERRYTREE,
        ];
    }

    public function getProviderByRequest(IRequest $request)
    {
        $paramKeys = array_keys($request->getParams());
        foreach ($this->supportedProviders() as $possibleProvider) {
            if (in_array($possibleProvider, $paramKeys, true)) {
                return $possibleProvider;
            }
        }
        throw new NotFoundException();
    }

    public function createProviderInstance($providerKey, $filesystemPathToStructure)
    {
        if ($providerKey === self::REQUEST_KEY_CHERRYTREE) {
            $instance = new CherryTreeStructure(Filesystem::getView(), $filesystemPathToStructure);
        } else {
            throw new WebException(sprintf('Unknown key %s', $providerKey));
        }
        return $instance;
    }
}