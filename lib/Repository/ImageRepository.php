<?php

namespace OCA\FractalNote\Repository;

use Doctrine\ORM\EntityRepository;
use OCA\FractalNote\Model\Image;
use OCA\FractalNote\Model\Node;

/**
 * Репозиторий для работы с сущностью Image
 */
class ImageRepository extends EntityRepository
{
    /**
     * Найти изображения по узлу (Node).
     *
     * @param Node $node
     * @return Image|null
     */
    public function findByNode(Node $node): ?Image
    {
        return $this->findOneBy(['node' => $node]);
    }

    /**
     * Сохранить изображение в базу данных.
     *
     * @param Image $image
     */
    public function save(Image $image): void
    {
        $this->_em->persist($image);
        $this->_em->flush();
    }

    /**
     * Удалить изображение из базы данных.
     *
     * @param Image $image
     */
    public function delete(Image $image): void
    {
        $this->_em->remove($image);
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
        $queryBuilder->select('MAX(i.' . $propertyName . ')')
            ->from(Image::class, 'i');

        $maxValue = $queryBuilder->getQuery()->getSingleScalarResult();

        // Возвращаем следующее значение, увеличенное на 1
        return (int)$maxValue + 1;
    }
}
