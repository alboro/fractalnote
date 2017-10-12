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
use OCP\AppFramework\Db\Entity as NativeEntity;

class BookmarkMapper extends Mapper
{
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'bookmark', '\OCA\FractalNote\Db\Bookmark');
    }

    /**
     * @return Bookmark[]|array
     */
    public function findBookmarks()
    {
        $sql = 'SELECT * FROM `' . $this->getTableName() . '`';
        return $this->findEntities($sql);
    }

    /**
     * @param $nodeId
     * @return Bookmark|null
     */
    public function findBookmark($nodeId)
    {
        $sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `node_id` = ?';
        return current($this->findEntities($sql, [$nodeId])) ? : null;
    }
}