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

class SqliteConnectionFactory
{
    /** @var \OC\DB\Connection[] */
    private static $connections = [];

    /**
     * @param $path
     *
     * @return \OC\DB\Connection
     * @throws \OC\DatabaseException
     */
    public static function getConnectionByPath($path)
    {
        if (empty(self::$connections[$path])) {
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
        }
        return self::$connections[$path];
    }
}