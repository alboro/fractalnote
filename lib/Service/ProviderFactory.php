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

use OCA\FractalNote\Service\Exception\WebException;
use \OCP\IDBConnection;
use \OC\Files\Filesystem;
use \OCA\FractalNote\Service\NotesStructure;
use \OCA\FractalNote\Provider\CherryTree\CherryTreeStructure;
use \OCA\FractalNote\Provider\CherryTree\Db\SqliteConnectionFactory;

class ProviderFactory
{
    public function createProviderInstance($providerKey, $filesystemPathToStructure)
    {
        if ($providerKey === \OCA\FractalNote\Controller\AbstractController::REQUEST_KEY_FILE_PATH) {
            $instance = new CherryTreeStructure(Filesystem::getView(), $filesystemPathToStructure);
        } else {
            throw new WebException(sprintf('Unknown key %s', $providerKey));
        }
        return $instance;
    }
}