<?php
/**
 * NextCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Provider\CherryTree;

use Closure;
use Throwable;
use Doctrine\ORM\EntityManager;
use OC\Files\View;
use OCA\FractalNote\Factory\DatabaseConnectionFactory;
use OCA\FractalNote\Model\Bookmark;
use OCA\FractalNote\Model\Relation;
use OCA\FractalNote\Model\Codebox;
use OCA\FractalNote\Model\Grid;
use OCA\FractalNote\Model\GridRepository;
use OCA\FractalNote\Model\Image;
use OCA\FractalNote\Model\Node;
use OCA\FractalNote\Repository\BookmarkRepository;
use OCA\FractalNote\Repository\RelationRepository;
use OCA\FractalNote\Repository\CodeboxRepository;
use OCA\FractalNote\Repository\ImageRepository;
use OCA\FractalNote\Repository\NodeRepository;
use OCA\FractalNote\Service\Exception\ConflictException;
use OCA\FractalNote\Service\Exception\NotFoundException;

class CherryTreeProvider
{
    private View $viewer;
    private EntityManager $entityManager;

    /**
     * CherryTreeProvider constructor.
     *
     * @throws NotFoundException
     */
    public function __construct(View $view, string $filePath)
    {
        $this->viewer = $view;
        $this->setFilesystemPathToStructure($filePath);
        if (!$filePath || !$this->viewer->is_file($filePath)) {
            throw new NotFoundException();
        }
        $this->entityManager = DatabaseConnectionFactory::createEntityManager($filePath);
    }

    private string $filesystemPathToStructure;

    public function getFilesystemPathToStructure(): string
    {
        return $this->filesystemPathToStructure;
    }

    private function decorateWithDbTransaction(Closure $closure): mixed
    {
        $db = $this->entityManager();
        $db->beginTransaction();
        try {
            $result = $closure();
        } catch (Throwable $throwable) {
            $db->rollBack();

            throw $throwable;
        }
        $db->commit();

        return $result;
    }

    public function createNode(
        string $parentId,
        string $title,
        int $position,
        int $mtime,
        string $content = ''
    ): string {
        if ($this->isExpired($parentId, $mtime)) {
            throw new ConflictException($title);
        }

        try {
            $this->lockResource();

            $nodeIdentifier = $this->decorateWithDbTransaction(
                static function () use ($parentId, $title, $position, $content) {
                    return $this->_createNode(
                        (int) $parentId,
                        $title,
                        $position,
                        $content,
                        false
                    );
                }
            );

            $this->unlockResource();
            $this->requireSync();
        } catch (Throwable $e) {
            $this->handleException($e);
        }

        return $nodeIdentifier;
    }

    public function updateNode(int $mtime, array $nodeData): void
    {
        $nodeId = array_key_exists('id', $nodeData) ? $nodeData['id'] : null;
        if (!$nodeId) {
            throw new NotFoundException();
        }
        if ($this->isExpired($nodeId, $mtime)) {
            throw new ConflictException();
        }
        try {
            $this->lockResource();

            $this->decorateWithDbTransaction(
                static function () use ($nodeId, $nodeData) {
                    $this->_updateNode(
                        $nodeId,
                        $nodeData['title'] ?? null,
                        $nodeData['content'] ?? null,
                        $nodeData['newParentId'] ?? null,
                        $nodeData['position'] ?? null
                    );
                }
            );

            $this->unlockResource();
            $this->requireSync();
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    public function delete(int $nodeId): void
    {
        try {
            $this->lockResource();
            $this->decorateWithDbTransaction(
                static function () use ($nodeId) {
                    $this->_delete($nodeId);
                }
            );
            $this->unlockResource();
            $this->requireSync();
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    protected function setFilesystemPathToStructure(string $filesystemPathToStructure): void
    {
        $this->filesystemPathToStructure = $filesystemPathToStructure;
    }

    private function entityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /*private function getAbsolutePath($file): string
    {
        if (!$file || !$this->viewer->is_file($file)) {
            throw new NotFoundException();
        }
        $postFix = ($file[strlen($file) -1] === '/') ? '/' : '';
        $relativeFilePath = $this->viewer->getAbsolutePath($file);
        list($storage, $internalPath) = Filesystem::resolvePath(
            $relativeFilePath . $postFix
        );

        return $storage->getLocalFile($internalPath);
    }*/

    public function requireSync()
    {
        $this->viewer->touch($this->getFilesystemPathToStructure());
    }

    public function lockResource()
    {
        $this->viewer->lockFile($this->getFilesystemPathToStructure(), \OCP\Lock\ILockingProvider::LOCK_SHARED, true);
    }

    public function unlockResource()
    {
        $this->viewer->unlockFile($this->getFilesystemPathToStructure(), \OCP\Lock\ILockingProvider::LOCK_SHARED, true);
    }

    public function getModifyTime()
    {
        return $this->viewer->filemtime($this->getFilesystemPathToStructure());
    }

    /**
     * @param integer|string $nodeId
     * @param integer        $storedExpiration
     *
     * @return mixed
     */
    public function isExpired($nodeId, $storedExpiration)
    {
        return $this->getModifyTime() !== $storedExpiration;
    }

    /**
     * @return Relation[]
     */
    public function buildRelationTree(): array
    {
        return $this->createRelationRepository()->buildTree();
    }

    private function updateRelationLevels(Node $node): void
    {
        $relationMapper = $this->createRelationRepository();
        $parentLevel = $node->level();
        $relations = $relationMapper->findByParent($node->nodeId());
        foreach ($relations as $relation) {
            $node = $relation->node();
            $node->assignLevelValue($parentLevel + 1);

            $this->createNodeRepository()->save($node);
            $this->updateRelationLevels($node);
        }
    }

    protected function _createNode(
        int $parentNodeId,
        string $title,
        int $position,
        string $content,
        bool $isRich
    ): string {
        $nodeMapper = $this->createNodeRepository();
        $relationMapper = $this->createRelationRepository();

        if ($parentNodeId !== 0) {
            $parentNode = $nodeMapper->findById($parentNodeId);
            if (null === $parentNode) {
                throw new NotFoundException();
            }
        }
        $id = $nodeMapper->calculateNextIncrementValue();
        $level = $relationMapper->calculateLevelByParentId($parentNodeId);
        $node = new Node(
            $id,
            $title,
            $content,
            'plain-text',
            '',
            false,
            $isRich,
            false,
            false,
            false,
            $level,
            time(),
            time(),
        );
        $nodeMapper->save($node);
        $relation = new Relation($node, $parentNodeId, $position);
        $relationMapper->save($relation);

        return (string) $id;
    }

    protected function _updateNode(
        int $nodeId,
        ?string $title,
        ?string $content,
        ?string $newParentId,
        ?string $position,
    ): void {
        $nodeMapper = $this->createNodeRepository();

        $node = $nodeMapper->findById($nodeId);

        if ($newParentId === null) {
            $node->update($title, $content);
            $nodeMapper->save($node);
        } else {
            $newParentNode = null;
            if ($newParentId !== 0) {
                $newParentNode = $nodeMapper->findById($newParentId);
                if (null === $newParentNode) {
                    throw new NotFoundException();
                }
            }
            $node->relation()->move($newParentNode, (int) $position);
            $relationMapper = $this->createRelationRepository();
            $node->assignLevelValue(
                $relationMapper->calculateLevelByParentId((int) $newParentId)
            );
            $nodeMapper->save($node);
            $this->updateRelationLevels($node);
        }
    }

    protected function _delete(int $nodeId): void
    {
        $relationMapper = $this->createRelationRepository();
        $nodeMapper = $this->createNodeRepository();
        $node = $nodeMapper->findById($nodeId);

        if ($node->level() === 0 && count($relationMapper->findByParent(0)) === 1) {
            throw new \LogicException('The only one top node cannot be deleted.');
        }
        $childrenRelations = $relationMapper->findByParent($nodeId);
        foreach ($childrenRelations as $relation) {
            $relation instanceof Relation && $this->_delete($relation->node()->nodeId());
        }
        $nodeMapper->delete($node);
    }

    protected function createNodeRepository(): NodeRepository
    {
        return $this->entityManager->getRepository(Node::class);
    }

    protected function createRelationRepository(): RelationRepository
    {
        return $this->entityManager->getRepository(Relation::class);
    }

    protected function createImageRepository():  ImageRepository
    {
        return $this->entityManager->getRepository(Image::class);
    }

    protected function createBookmarkRepository(): BookmarkRepository
    {
        return $this->entityManager->getRepository(Bookmark::class);
    }

    protected function createGridRepository(): GridRepository
    {
        return $this->entityManager->getRepository(Grid::class);
    }

    protected function createCodeRepository(): CodeboxRepository
    {
        return $this->entityManager->getRepository(Codebox::class);
    }

    protected function handleException(Throwable $e, $resourceLocked = true)
    {
        if ($resourceLocked) {
            $this->unlockResource();
        }
        throw new NotFoundException($e->getMessage());
    }
}
