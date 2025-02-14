<?php

namespace todolist\system\worker;

use todolist\data\todo\TodoEditor;
use todolist\data\todo\TodoList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\search\SearchIndexManager;
use wcf\system\WCF;
use wcf\system\worker\AbstractRebuildDataWorker;

/**
 * Worker implementation for updating todo.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package     de.julian-pfeil.todolist
 * @subpackage  system.worker
 */

class TodolistRebuildDataWorker extends AbstractRebuildDataWorker
{
    /**
     * @var HtmlInputProcessor
     */
    protected $htmlInputProcessor;

    /**
     * @inheritDoc
     */
    protected $limit = 100;

    /**
     * @inheritDoc
     */
    protected $objectListClassName = TodoList::class;

    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();
        
        if (!$this->loopCount) {
            // reset search index
            SearchIndexManager::getInstance()->reset('de.julian-pfeil.todolist.todo');
        }

        if (!\count($this->objectList)) {
            return;
        }

        // fetch cumulative likes
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("objectTypeID = ?", [ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.like.likeableObject', 'com.uz.todolist.likeableTodo')]);
        $conditions->add("objectID IN (?)", [$this->objectList->getObjectIDs()]);
        $sql = "SELECT	objectID, cumulativeLikes
                FROM	wcf1_like_object
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $cumulativeLikes = $statement->fetchMap('objectID', 'cumulativeLikes');

        // prepare statements
        $commentObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.comment.commentableContent', 'de.julian-pfeil.todolist.todoComment');
        $sql = "SELECT	COUNT(*) AS comments, SUM(responses) AS responses
                FROM	wcf1_comment
                WHERE	objectTypeID = ? AND objectID = ?";
        $commentStatement = WCF::getDB()->prepare($sql);
        WCF::getDB()->beginTransaction();
        foreach ($this->objectList as $todo) {
            $editor = new TodoEditor($todo);
            $data = [];

            // count comments
            $commentStatement->execute([$commentObjectType->objectTypeID, $todo->todoID]);
            $row = $commentStatement->fetchSingleRow();
            $data['comments'] = $row['comments'] + $row['responses'];

            // update cumulative likes
            $data['cumulativeLikes'] = $cumulativeLikes[$todo->todoID] ?? 0;

            // update description
            if ($todo->description != '') {
                $this->getHtmlInputProcessor()->reprocess($todo->description, 'de.julian-pfeil.todolist.todo.content', $todo->todoID);
                $data['description'] = $this->getHtmlInputProcessor()->getHtml();
                if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->getHtmlInputProcessor())) {
                    $data['hasEmbeddedObjects'] = 1;
                } else {
                    $data['hasEmbeddedObjects'] = 0;
                }
            } else {
                $data['hasEmbeddedObjects'] = 0;
            }

            $editor->update($data);

            SearchIndexManager::getInstance()->set(
                'de.julian-pfeil.todolist.todo',
                $todo->todoID,
                $todo->description,
                $todo->getTitle(),
                $todo->time,
                $todo->userID,
                $todo->username
            );
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * @return HtmlInputProcessor
     */
    protected function getHtmlInputProcessor()
    {
        if ($this->htmlInputProcessor === null) {
            $this->htmlInputProcessor = new HtmlInputProcessor();
        }

        return $this->htmlInputProcessor;
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();
        $this->objectList->sqlOrderBy = 'todo.todoID';
    }
}
