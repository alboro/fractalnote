<?php

namespace OCA\FractalNote\Repository;

use Doctrine\ORM\EntityRepository;
use OCA\FractalNote\Model\Bookmark;
use OCA\FractalNote\Model\Node;

/**
 * Репозиторий для работы с сущностью Bookmark
 */
class BookmarkRepository extends EntityRepository
{
    /**
     * Найти Bookmark по узлу (Node).
     *
     * @param Node $node
     * @return Bookmark|null
     */
    public function findByNode(Node $node): ?Bookmark
    {
        return $this->findOneBy(['node' => $node]);
    }

    /**
     * Сохранить Bookmark в базу данных.
     *
     * @param Bookmark $bookmark
     */
    public function save(Bookmark $bookmark): void
    {
        $this->_em->persist($bookmark);
        $this->_em->flush();
    }

    /**
     * Удалить Bookmark из базы данных.
     *
     * @param Bookmark $bookmark
     */
    public function delete(Bookmark $bookmark): void
    {
        $this->_em->remove($bookmark);
        $this->_em->flush();
    }
}
