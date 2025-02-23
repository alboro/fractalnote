<?php

namespace OCA\FractalNote\Repository;

use Doctrine\ORM\EntityRepository;
use OCA\FractalNote\Model\Node;

/**
 * Репозиторий для работы с сущностью Node
 */
class NodeRepository extends EntityRepository
{
    /**
     * Найти узел по его идентификатору.
     *
     * @param int $nodeId
     * @return Node|null
     */
    public function findById(int $nodeId): ?Node
    {
        return $this->find($nodeId);
    }

    /**
     * Найти все узлы.
     *
     * @return Node[]
     */
    public function findAllNodes(): array
    {
        return $this->findAll();
    }

    /**
     * Найти узлы по имени.
     *
     * @param string $name
     * @return Node[]
     */
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getResult();
    }

    /**
     * Сохранить узел в базу данных.
     *
     * @param Node $node
     */
    public function save(Node $node): void
    {
        $this->_em->persist($node);
        $this->_em->flush();
    }

    /**
     * Удалить узел из базы данных.
     *
     * @param Node $node
     */
    public function delete(Node $node): void
    {
        $this->_em->remove($node);
        $this->_em->flush();
    }

    /**
     * Рассчитать следующее инкрементное значение для указанного поля.
     *
     * @param string|null $propertyName
     * @return int
     */
    public function calculateNextIncrementValue($propertyName = null): int
    {
        // Если свойство не указано, используем "nodeId" как свойство по умолчанию
        $propertyName = $propertyName ?? 'nodeId';

        // Получаем максимальное значение указанного свойства
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('MAX(n.' . $propertyName . ')')
            ->from(Node::class, 'n');

        $maxValue = $queryBuilder->getQuery()->getSingleScalarResult();

        // Возвращаем следующее значение, увеличенное на 1
        return (int)$maxValue + 1;
    }
}
