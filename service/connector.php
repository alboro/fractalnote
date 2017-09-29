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

use \OCP\IDBConnection;
use \OC\Files\Filesystem;
use \OCA\FractalNote\Db\SqliteConnectionFactory;

class Connector
{
    private $filePath;
    private $db;
    private $viewer;

    /**
     * @param IDBConnection $db
     * @param string        $filePath
     *
     * @return self
     */
    public function __construct(IDBConnection $db = null, $filePath = null)
    {
        $this->db       = $db;
        $this->filePath = $filePath;
        $this->viewer   = Filesystem::getView();
    }

    public function isConnected()
    {
        return $this->filePath && $this->db instanceof IDBConnection;
    }

    /**
     * @return IDBConnection
     */
    public function getDb()
    {
        return $this->db;
    }

    public function requireSync()
    {
        $this->viewer->touch($this->filePath);
        // $viewer->putFileInfo($this->getFilePath(), array('mtime' => time()));
    }

    public function lockResource()
    {
        $this->viewer->lockFile($this->filePath, \OCP\Lock\ILockingProvider::LOCK_SHARED, true);
    }

    public function unlockResource()
    {
        $this->viewer->unlockFile($this->filePath, \OCP\Lock\ILockingProvider::LOCK_SHARED, true);
    }

    public function getModifyTime()
    {
        return $this->viewer->filemtime($this->filePath);
    }

    /**
     * @param $file
     *
     * @return self
     */
    public static function run($file)
    {
        if (!$file || !\OC\Files\Filesystem::is_file($file)) {
            return new self();
        }
        $postFix = ($file[strlen($file) -1] === '/') ? '/' : '';
        $viewer = \OC\Files\Filesystem::getView();
        $relativeFilePath = $viewer->getAbsolutePath($file);
        list($storage, $internalPath) = \OC\Files\Filesystem::resolvePath(
            $relativeFilePath . $postFix
        );
        $filePath = $storage->getLocalFile($internalPath);
        return new self(SqliteConnectionFactory::getConnectionByPath($filePath), $file);
    }
}