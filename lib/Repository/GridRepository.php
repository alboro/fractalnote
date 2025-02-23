<?php

namespace OCA\FractalNote\Model;

use Doctrine\ORM\EntityRepository;
use OCA\FractalNote\Model\Grid;
use OCA\FractalNote\Model\Node;

/**
 * Репозиторий для работы с сущностью Grid
 */
class GridRepository extends EntityRepository
{
    /**
     * Найти Grid по узлу (Node).
     *
     * @param Node $node
     * @return Grid|null
     */
    public function findByNode(Node $node): ?Grid
    {
        return $this->findOneBy(['node' => $node]);
    }

    /**
     * Сохранить Grid в базу данных.
     *
     * @param Grid $grid
     */
    public function save(Grid $grid): void
    {
        $this->_em->persist($grid);
        $this->_em->flush();
    }

    /**
     * Удалить Grid из базы данных.
     *
     * @param Grid $grid
     */
    public function delete(Grid $grid): void
    {
        $this->_em->remove($grid);
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
        $queryBuilder->select('MAX(g.' . $propertyName . ')')
            ->from(Grid::class, 'g');

        $maxValue = $queryBuilder->getQuery()->getSingleScalarResult();

        // Возвращаем следующее значение, увеличенное на 1
        return (int)$maxValue + 1;
    }
}
