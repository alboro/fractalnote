<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Service;

use Exception;
use OCA\FractalNote\Db\CodeboxMapper;
use OCA\FractalNote\Db\GridMapper;
use OCA\FractalNote\Db\Node;
use OCA\FractalNote\Db\NodeMapper;
use OCA\FractalNote\Db\Relation;
use OCA\FractalNote\Db\RelationMapper;
use OCA\FractalNote\Db\ImageMapper;
use OCA\FractalNote\Db\BookmarkMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

class NotesStructure
{

    /** @var Connector */
    private $connector;

    /**
     * @param Connector $connector
     *
     * @return NotesStructure
     */
    public function setConnector(Connector $connector)
    {
        $this->connector = $connector;
        return $this;
    }

    public function buildTree()
    {
        return $this->createRelationMapper()->buildTree();
    }

    public function findNode($id)
    {
        try {
            $node = $this->createNodeMapper()->find($id);

            // in order to be able to plug in different storage backends like files
            // for instance it is a good idea to turn storage related exceptions
            // into service related exceptions so controllers and service users
            // have to deal with only one type of exception
        } catch (Exception $e) {
            $this->handleException($e);
        }

        return $node;
    }

    /**
     * @param integer $parentId
     * @param string  $title
     * @param integer $position
     * @param string  $content
     * @param string  $syntax
     * @param integer $isRich
     *
     * @return mixed node identifier
     */
    public function createNode(
        $parentId = 0,
        $title = 'New node',
        $position = 0,
        $content = '',
        $syntax = 'plain-text',
        $isRich = 0
    ) {
        try {
            $this->connector->lockResource();

            $db = $this->connector->getDb();
            $db->beginTransaction();
            $nodeMapper = $this->createNodeMapper();
            $relationMapper = $this->createRelationMapper();

            $note = Node::factory();
            $note->setName($title);
            $note->setTxt($content);
            $note->setSyntax($syntax);
            $note->setIsRichtxt((bool)$isRich);
            $note->setLevel($relationMapper->calculateLevelByParentId($parentId));
            $note->setId($nodeMapper->calculateNextIncrementValue());
            $nodeMapper->insert($note);

            $child = new Relation();
            $child->setNode($note);
            $child->setFatherId($parentId);
            $child->setSequence($position);
            $relationMapper->insert($child);

            $db->commit();
            $nodeIdentifier = $child->getNodeId();

            $this->connector->unlockResource();
            $this->connector->requireSync();
        } catch (Exception $e) {
            isset($db) && $db->rollBack();
            $this->connector->unlockResource();
            $this->handleException($e);
        }

        return $nodeIdentifier;
    }

    /**
     * @param integer $nodeId
     * @param integer $newParentId
     * @param integer $sequence
     *
     * @return Relation
     */
    protected function move($nodeId, $newParentId, $sequence)
    {
        if ((int)$newParentId < 0) {
            throw new WebException('Passed parent node is out of range');
        }
        $relationMapper = $this->createRelationMapper();
        $relation = $relationMapper->find($nodeId); /* @var $relation Relation */
        $newParentId === 0 || $this->createNodeMapper()->find($newParentId);

        $relation->setFatherId($newParentId);
        null !== $sequence && $relation->setSequence($sequence);
        if (!$relation->getUpdatedFields()) {
            throw new NoChangesException();
        }
        $relationMapper->update($relation);

        return $relation;
    }

    public function updateNode($nodeIdentifier, $title, $content, $newParentId, $position)
    {
        $nodeMapper = $this->createNodeMapper();
        try {
            $this->connector->lockResource();
            $db = $this->connector->getDb();
            $db->beginTransaction();

            $note = $nodeMapper->find($nodeIdentifier); /* @var Node $note */

            if ($newParentId === null) {
                if (!$note->isEditable()) {
                    throw new NotEditableException($note->isRich(), $note->isReadOnly());
                }
                null !== $title && $note->setName($title);
                null !== $content && $note->setTxt($content);
                if (!$note->getUpdatedFields()) {
                    throw new NoChangesException();
                }
            } elseif (isset($newParentId)) {
                $relationMapper = $this->createRelationMapper();
                $this->move($nodeIdentifier, $newParentId, $position);
                $note->setLevel($relationMapper->calculateLevelByParentId((int)$newParentId));
                $this->updateChildRelationLevels($note);
            }
            // make changes
            $nodeMapper->update($note);

            $db->commit();
            $this->connector->unlockResource();
            $this->connector->requireSync();
        } catch (Exception $e) {
            isset($db) && $db->rollBack();
            $this->handleException($e);
        }
        return $note;
    }

    public function updateChildRelationLevels(Node $node)
    {
        $relationMapper = $this->createRelationMapper();
        $parentLevel = $node->getLevel();
        $childRelations = $relationMapper->findChildRelationsWithNodes($node->getId());
        foreach ($childRelations as $relation) {
            $relation->getNode()->setLevel($parentLevel + 1);
            $this->createNodeMapper()->update($relation->getNode());
            $this->updateChildRelationLevels($relation->getNode());
        }
    }

    /**
     * @param $noteId
     */
    public function delete($noteId)
    {
        try {
            $this->connector->lockResource();
            $db = $this->connector->getDb();
            $db->beginTransaction();

            $this->_delete($noteId);

            $db->commit();
            $this->connector->unlockResource();
            $this->connector->requireSync();
        } catch (Exception $e) {
            isset($db) && $db->rollBack();
            $this->handleException($e);
        }
    }

    /**
     * @param integer $noteId
     */
    private function _delete($noteId)
    {
        $relationMapper = $this->createRelationMapper();
        $nodeMapper = $this->createNodeMapper();
        $relation = $relationMapper->find($noteId); /** @var $relation Relation */
        $note = $nodeMapper->find($noteId); /** @var $note Node */
        $childRelations = $relationMapper->findChildRelations($noteId);
        foreach ($childRelations as $childRelation) {
            $childRelation instanceof Relation && $this->_delete($childRelation->getNodeId());
        }
        $relationMapper->delete($relation);

        $bookmarkMapper = $this->createBookmarkMapper();
        $bookmark = $bookmarkMapper->findBookmark($note->getId());
        $bookmark && $bookmarkMapper->delete($bookmark);

        if ($note->isRich()) {

            $imageMapper = $this->createImageMapper();
            $images = $imageMapper->findImages($note->getId());
            foreach ($images as $image) {
                $imageMapper->delete($image);
            }

            $codeMapper = $this->createCodeMapper();
            $codeboxes = $codeMapper->findCodeboxes($note->getId());
            foreach ($codeboxes as $codebox) {
                $codeMapper->delete($codebox);
            }

            $gridMapper = $this->createGridMapper();
            $grids = $gridMapper->findGrids($note->getId());
            foreach ($grids as $grid) {
                $gridMapper->delete($grid);
            }
        }
        $nodeMapper->delete($note);
    }

    protected function createNodeMapper()
    {
        return new NodeMapper($this->connector->getDb());
    }

    protected function createRelationMapper()
    {
        return new RelationMapper($this->connector->getDb());
    }

    protected function createImageMapper()
    {
        return new ImageMapper($this->connector->getDb());
    }

    protected function createBookmarkMapper()
    {
        return new BookmarkMapper($this->connector->getDb());
    }

    protected function createGridMapper()
    {
        return new GridMapper($this->connector->getDb());
    }

    protected function createCodeMapper()
    {
        return new CodeboxMapper($this->connector->getDb());
    }

    private function handleException($e)
    {
        if ($e instanceof DoesNotExistException || $e instanceof MultipleObjectsReturnedException) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }
}
