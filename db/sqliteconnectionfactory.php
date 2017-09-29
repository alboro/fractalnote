<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Db;

use OC\Db\ConnectionFactory as ConnectionFactory;
use OC\SystemConfig;

/**
 */
class SqliteConnectionFactory extends ConnectionFactory
{
    /** @var \OC\DB\Connection[] */
    private static $connections = [];

    /**
     * SqliteConnectionFactory constructor.
     *
     * @param SystemConfig|null $systemConfig
     */
    public function __construct(SystemConfig $systemConfig = null)
    {
    }

    /**
     * @param $path
     *
     * @return \OC\DB\Connection
     * @throws \OC\DatabaseException
     */
    public function getSqliteConnectionByPath($path)
    {
        $type = 'sqlite3';
        if (!$this->isValidType($type)) {
            throw new \OC\DatabaseException('Invalid database type');
        }
        $connection = $this->getConnection($type,
            [
                'tablePrefix'         => '',
                // 'sqlite.journal_mode' => 'WAL',
                'sqlite.journal_mode' => 'DELETE',
                'path'                => $path,
            ]);

        return $connection;
    }

    /**
     *
     * @return \OC\DB\Connection
     */
    public static function getConnectionByPath($path)
    {
        if (empty(self::$connections[$path])) {
            self::$connections[$path] = (new self())->getSqliteConnectionByPath($path);
        }

        return self::$connections[$path];
    }

}
