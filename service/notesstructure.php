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
use OCA\FractalNote\Db\Node;
use OCA\FractalNote\Db\NodeMapper;
use OCA\FractalNote\Db\Relation;
use OCA\FractalNote\Db\RelationMapper;
use OCA\FractalNote\Db\ImageMapper;
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
        $shuffledChildren = $this->createRelationMapper()->findChildrenWithNodes();
        $children = [];
        foreach ($shuffledChildren as $k => $child) {
            /* @var $child Relation */
            $children[$child->getNodeId()] = $child;
        }
        foreach ($children as $nodeId => $child) {
            /* @var $child Relation */
            $fatherId = $child->getFatherId();
            if ($fatherId && isset($children[$fatherId])) {
                $father = $children[$fatherId];
                /* @var $father Relation */
                $father->addChild($child);
            }
        }
        $children = array_filter($children, function (Relation $v) {
            return !$v->getFatherId();
        });

        return array_values($children);
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
     * @param integer $sequence
     * @param string  $content
     * @param string  $syntax
     * @param integer $isRich
     *
     * @return Relation|void
     */
    public function create(
        $parentId = 0,
        $title = 'New node',
        $sequence = 0,
        $content = '',
        $syntax = 'plain-text',
        $isRich = 0
    ) {
        $note = Node::factory();
        $note->setName($title);
        $note->setTxt($content);
        $note->setSyntax($syntax);
        $note->setIsRichtxt((bool)$isRich);

        $db = $this->connector->getDb();
        $nodeMapper = $this->createNodeMapper();
        $relationMapper = $this->createRelationMapper();
        try {
            $this->connector->lockResource();
            $db->beginTransaction();

            $note->setLevel($relationMapper->calculateLevelByParentId($parentId));
            $note->setId($nodeMapper->calculateNextIncrementValue());

            $nodeMapper->insert($note);

            $child = new Relation();
            $child->setNode($note);
            $child->setFatherId($parentId);
            $child->setSequence($sequence);
            $this->createRelationMapper()->insert($child);

            $db->commit();

            $this->connector->unlockResource();
            $this->connector->requireSync();
        } catch (Exception $e) {
            $db->rollBack();
            $this->connector->unlockResource();

            return $this->handleException($e);
        }

        return $child;
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
        $relation = $relationMapper->find($nodeId);
        $this->createNodeMapper()->find($newParentId);

        $relation->setFatherId($newParentId);
        null !== $sequence && $relation->setSequence($sequence);
        if (!$relation->getUpdatedFields()) {
            throw new WebException('No any changes done');
        }
        $relationMapper->update($relation);

        return $relation;
    }

    public function update($nodeId, $title, $content, $newParentId, $sequence)
    {
        $db = $this->connector->getDb();
        $nodeMapper = $this->createNodeMapper();
        try {
            $this->connector->lockResource();
            $db->beginTransaction();

            $note = $nodeMapper->find($nodeId);

            if ($newParentId === null) {
                if (!$note->isEditable()) {
                    throw new NotEditableException($note->isRich(), $note->isReadOnly());
                }
                null !== $title && $note->setName($title);
                null !== $content && $note->setTxt($content);
                if (!$note->getUpdatedFields()) {
                    throw new WebException('No any changes done');
                }
            } elseif (isset($newParentId)) {
                $relationMapper = $this->createRelationMapper();
                $this->move($nodeId, $newParentId, $sequence);
                $note->setLevel($relationMapper->calculateLevelByParentId($newParentId));
                $this->updateChildRelationLevels($note);
            }
            // make changes
            $nodeMapper->update($note);

            $db->commit();
            $this->connector->unlockResource();
            $this->connector->requireSync();
        } catch (Exception $e) {
            $db->rollBack();
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
        $db = $this->connector->getDb();
        try {
            $this->connector->lockResource();
            $db->beginTransaction();

            $this->_delete($noteId);

            $db->commit();
            $this->connector->unlockResource();
            $this->connector->requireSync();
        } catch (Exception $e) {
            $db->rollBack();
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
        if ($note->isRich()) {
            $imageMapper = $this->createImageMapper();
            $images = $imageMapper->findImages($note->getId());
            foreach ($images as $image) {
                $imageMapper->delete($image);
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

    private function handleException($e)
    {
        if ($e instanceof DoesNotExistException || $e instanceof MultipleObjectsReturnedException) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }
}
