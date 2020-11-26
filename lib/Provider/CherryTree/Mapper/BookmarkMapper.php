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
     * @return Bookmark[]
     */
    public function findBookmarks(): array
    {
        return $this->findEntities(
            $this->db->getQueryBuilder()->select('*')->from($this->getTableName())
        );
    }

    public function findBookmark($nodeId): ?Bookmark
    {
        $q = $this->db->getQueryBuilder()
            ->select('*')
            ->from($this->getTableName())
            ->where('node_id = ' . $this->db->quote($nodeId))
        ;
        return current($this->findEntities($q)) ? : null;
    }
}