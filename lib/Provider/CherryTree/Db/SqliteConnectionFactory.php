<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Provider\CherryTree\Db;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\DBALException;
use OCA\FractalNote\Service\Exception\NotFoundException;

class SqliteConnectionFactory
{
    /** @var \OC\DB\Connection[] */
    private static $connections = [];

    /**
     * @param $path
     *
     * @return \OC\DB\Connection
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function getConnectionByPath($path)
    {
        if (empty(self::$connections[$path])) {
            try {
                self::$connections[$path] = DriverManager::getConnection(
                    [
                        'adapter'             => '\OC\DB\AdapterSqlite',
                        'driver'              => 'pdo_sqlite',
                        'wrapperClass'        => 'OC\DB\Connection',
                        'tablePrefix'         => '',
                        'sqlite.journal_mode' => 'DELETE',
                        'path'                => $path,
                    ]
                );
            } catch (DBALException $dbalException) {
                throw new NotFoundException($dbalException->getMessage());
            }
        }
        return self::$connections[$path];
    }
}