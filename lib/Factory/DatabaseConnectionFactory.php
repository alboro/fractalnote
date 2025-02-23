<?php

namespace OCA\FractalNote\Factory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use OC\DB\Exceptions\DbalException;

class DatabaseConnectionFactory
{
    /**
     * Создает и возвращает соединение с базой данных
     *
     * @param array $params Параметры для подключения к базе данных
     * @return Connection
     * @throws DBALException
     */
    private static function getConnection(array $params): Connection
    {
        // Пример параметров для подключения
        // $params = [
        //     'dbname'   => 'your_db_name',
        //     'user'     => 'your_db_user',
        //     'password' => 'your_db_password',
        //     'host'     => 'localhost',
        //     'driver'   => 'pdo_sqlite' или 'pdo_mysql', в зависимости от используемого драйвера
        // ];

        return DriverManager::getConnection($params);
    }

    public static function createEntityManager(string $dbPath): EntityManager
    {
        $connectionParams = [
            'path' => $dbPath,
            'driver' => 'pdo_sqlite',
        ];

        $connection = self::getConnection($connectionParams);
        $config = Setup::createXMLMetadataConfiguration([__DIR__ . '/../../config/doctrine'], true);

        return new EntityManager($connection, $config);
    }
}
