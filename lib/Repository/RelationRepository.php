<?php

namespace OCA\FractalNote\Repository;

use Doctrine\ORM\EntityRepository;
use OCA\FractalNote\Model\Relation;
use OCA\FractalNote\Model\Node;

/**
 * Репозиторий для работы с сущностью Children
 */
class RelationRepository extends EntityRepository
{
    /**
     * Найти сущность Children по nodeId.
     *
     * @param int $nodeId
     * @return Relation|null
     */
    public function findByNodeId(int $nodeId): ?Relation
    {
        return $this->findOneBy(['node' => $nodeId]);
    }

    /**
     * Найти все сущности Children.
     *
     * @return Relation[]
     */
    public function findAllChildren(): array
    {
        return $this->findAll();
    }

    /**
     * Найти все Relation с заданным parent (родителем)
     *
     * @param int $parentId Идентификатор родителя (parent)
     * @return Relation[] Массив объектов Relation
     */
    public function findByParent(int $parentId): array
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.parent = :parentId')
            ->setParameter('parentId', $parentId);

        return $qb->getQuery()->getResult();
    }

    /**
     * Сохранить сущность Children в базу данных.
     *
     * @param Relation $children
     */
    public function save(Relation $children): void
    {
        $this->_em->persist($children);
        $this->_em->flush();
    }

    /**
     * Удалить сущность Children из базы данных.
     *
     * @param Relation $children
     */
    public function delete(Relation $children): void
    {
        $this->_em->remove($children);
        $this->_em->flush();
    }

    /**
     * Рассчитать следующее инкрементное значение для указанного свойства.
     *
     * @param string|null $propertyName
     * @return int
     */
    public function calculateNextIncrementValue($propertyName = null): int
    {
        // Если свойство не указано, используем "sequence" по умолчанию
        $propertyName = $propertyName ?? 'sequence';

        // Получаем максимальное значение указанного свойства
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('MAX(c.' . $propertyName . ')')
            ->from(Relation::class, 'c');

        $maxValue = $queryBuilder->getQuery()->getSingleScalarResult();

        // Возвращаем следующее значение, увеличенное на 1
        return (int)$maxValue + 1;
    }

    private function findChildrenWithNodes()
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c', 'n')
            ->join('c.node', 'n')
            ->orderBy('n.level', 'ASC')
            ->addOrderBy('c.sequence', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function buildTree()
    {
        $children = $this->findChildrenWithNodes();
        $childrenIndex = [];
        foreach ($children as $child) {
            /* @var $child \OCA\FractalNote\Model\Relation */
            $childrenIndex[$child->node()->nodeId()] = $child;
            $child->parent()->relation()->addChild($child);
        }
        $childrenIndex = array_filter($childrenIndex, function (Relation $v) {
            return !$v->getFatherId();
        });
        // @todo: try to return just $children

        return array_values($childrenIndex);
    }


    /**
     * @TODO: turn into DAO
     */
    public function calculateLevelByParentId(int $parentId): int
    {
        if ($parentId === 0) {
            return 0;
        }
        $relation = $this->findByNodeId($parentId);
        return 1 + $this->calculateLevelByParentId($relation->parent()->nodeId());
    }
}
