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

use OCP\IDBConnection;

class NodeMapper extends Mapper
{
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'node', '\OCA\FractalNote\Db\Node');
    }
}
