<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Provider\CherryTree\Mapper;

use OCP\IDBConnection;
use OCA\FractalNote\Provider\CherryTree\Entity\Bookmark;
use OCA\FractalNote\Provider\CherryTree\Db\Mapper;

class BookmarkMapper extends Mapper
{
    /**
     * BookmarkMapper constructor.
     *
     * @param IDBConnection $db
     */
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'bookmark', Bookmark::class);
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