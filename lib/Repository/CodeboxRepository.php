<?php

namespace OCA\FractalNote\Repository;

use Doctrine\ORM\EntityRepository;
use OCA\FractalNote\Model\Codebox;
use OCA\FractalNote\Model\Node;

/**
 * Репозиторий для работы с сущностью Codebox
 */
class CodeboxRepository extends EntityRepository
{
    /**
     * Найти Codebox по узлу (Node).
     *
     * @param Node $node
     * @return Codebox|null
     */
    public function findByNode(Node $node): ?Codebox
    {
        return $this->findOneBy(['node' => $node]);
    }

    /**
     * Сохранить Codebox в базу данных.
     *
     * @param Codebox $codebox
     */
    public function save(Codebox $codebox): void
    {
        $this->_em->persist($codebox);
        $this->_em->flush();
    }

    /**
     * Удалить Codebox из базы данных.
     *
     * @param Codebox $codebox
     */
    public function delete(Codebox $codebox): void
    {
        $this->_em->remove($codebox);
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
        // Если свойство не указано, используем "offset" по умолчанию
        $propertyName = $propertyName ?? 'offset';

        // Получаем максимальное значение указанного свойства
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('MAX(c.' . $propertyName . ')')
            ->from(Codebox::class, 'c');

        $maxValue = $queryBuilder->getQuery()->getSingleScalarResult();

        // Возвращаем следующее значение, увеличенное на 1
        return (int)$maxValue + 1;
    }
}
