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

    /*public function findAll()
    {
        return $this->createNodeMapper()->findAll();
    }*/

    public function buildTree()
    {
        $shuffledChildren = $this->createChildMapper()->findChildrenWithNodes();
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
        $db = $this->connector->getDb();
        $note = Node::factory();
        $note->setName($title);
        $note->setTxt($content);
        $note->setSyntax($syntax);
        $note->setIsRichtxt((int)(bool)$isRich);

        try {
            $db->beginTransaction();
            $this->connector->lockResource();
            // @todo: calculate level
            $note->setId(
                $this->createNodeMapper()->calculateNextIncrementValue()
            );

            $this->createNodeMapper()->insert($note);

            $child = new Relation();
            $child->setNode($note);
            $child->setFatherId($parentId);
            $child->setSequence($sequence);
            $this->createChildMapper()->insert($child);

            $db->commit();

            $this->connector->requireSync();
            $this->connector->unlockResource();
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
     * @param null    $sequence
     *
     * @return Relation
     */
    public function move($nodeId, $newParentId, $sequence = null)
    {
        $mapper = $this->createChildMapper();
        try {
            $this->connector->lockResource();
            $relation = $mapper->find($nodeId);
            if (!$relation instanceof Relation) {
                throw new NotFoundException();
            }
            if (!$this->createNodeMapper()->find($newParentId) instanceof Node) {
                throw new WebException('Passed parent node doesn\'t exist');
            }

            $relation->setFatherId($newParentId);
            null !== $sequence && $relation->setSequence($sequence);
            $mapper->update($relation);

            $this->connector->requireSync();
            $this->connector->unlockResource();
        } catch (Exception $e) {
            $this->handleException($e);
        }
        return $relation;
    }

    public function update($id, $title, $content)
    {
        $syntax = 'plain-text';
        $isRich = 0;
        $mapper = $this->createNodeMapper();
        try {
            $this->connector->lockResource();
            $note = $mapper->find($id);
            if (!$note instanceof Node) {
                throw new NotFoundException();
            }
            if ($title === $note->getName() && $content === $note->getTxt()) {
                throw new WebException('No changes done');
            }
            if (!$note->isEditable()) {
                throw new NotEditableException($note->isRich(), $note->isReadOnly());
            }
            null !== $title && $note->setName($title);
            null !== $content && $note->setTxt($content);
            $note->setSyntax($syntax);
            $note->setIsRichtxt((int)(bool)$isRich);

            // make changes
            $mapper->update($note);

            $this->connector->requireSync();
            $this->connector->unlockResource();
        } catch (Exception $e) {
            $this->handleException($e);
        }
        return $note;
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
            $this->connector->requireSync();
            $this->connector->unlockResource();
        } catch (Exception $e) {
            $db->rollBack();
            $this->handleException($e);
        }
    }

    /**
     * @param $noteId
     */
    private function _delete($noteId)
    {
        $relationMapper = $this->createChildMapper();
        $nodeMapper = $this->createNodeMapper();
        $childRelations = $relationMapper->findNodeChildRelations($noteId);
        foreach ($childRelations as $childRelation) {
            $childRelation instanceof Relation && $this->_delete($childRelation->getNodeId());
        }
        $relation = $relationMapper->find($noteId);
        $note = $nodeMapper->find($noteId);

        $relation instanceof Relation && $relationMapper->delete($relation);
        $note instanceof Node && $nodeMapper->delete($note);
    }

    protected function createNodeMapper()
    {
        return new NodeMapper($this->connector->getDb());
    }

    protected function createChildMapper()
    {
        return new RelationMapper($this->connector->getDb());
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
