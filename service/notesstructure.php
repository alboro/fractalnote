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

    public function findAll()
    {
        return $this->createNodeMapper()->findAll();
    }

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

    public function find($id)
    {
        try {
            $node = $this->createNodeMapper()->find($id);

            // in order to be able to plug in different storage backends like files
            // for instance it is a good idea to turn storage related exceptions
            // into service related exceptions so controllers and service users
            // have to deal with only one type of exception
        } catch (Exception $e) {
            return $this->handleException($e);
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
        try {
            $this->connector->lockResource();
            $db->beginTransaction();

            $note = Node::factory();
            $note->setName($title);
            $note->setTxt($content);
            $note->setSyntax($syntax);
            $note->setIsRichtxt((int)(bool)$isRich);
            // todo: calculate level
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

    public function update($id, $title, $content)
    {
        $syntax = 'plain-text';
        $isRich = 0;
        $mapper = $this->createNodeMapper();
        try {
            $note = $mapper->find($id);
            if (!$note->isEditable()) {
                throw new NotEditableException($note->isRich(), $note->isReadOnly());
            }
            null !== $title && $note->setName($title);
            null !== $content && $note->setTxt($content);
            $note->setSyntax($syntax);
            $note->setIsRichtxt((int)(bool)$isRich);
            // make changes
            $this->connector->lockResource();
            $mapper->update($note);
            unset($mapper);
            $this->connector->requireSync();
            $this->connector->unlockResource();
        } catch (Exception $e) {
            $this->handleException($e);
        }
        return $note;
    }

    /**
     * @param $id
     *
     * @return Node
     */
    public function delete($id)
    {
        try {
            $note = $this->createNodeMapper()->find($id, $userId = 0);
            $this->createNodeMapper()->delete($note);

            return $note;
        } catch (Exception $e) {
            $this->handleException($e);
        }
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
