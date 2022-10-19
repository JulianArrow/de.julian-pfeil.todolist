<?php

namespace todolist\system\worker;

use todolist\data\todo\TodoEditor;
use todolist\data\todo\list\TodoList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\search\SearchIndexManager;
use wcf\system\worker\AbstractRebuildDataWorker;
use wcf\system\WCF;

/**
 * Worker implementation for updating todo.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.worker
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

        if (!count($this->objectList)) {
            return;
        }

        // fetch cumulative likes
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("objectTypeID = ?", [ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.like.likeableObject', 'com.uz.todolist.likeableTodo')]);
        $conditions->add("objectID IN (?)", [$this->objectList->getObjectIDs()]);
        $sql = "SELECT	objectID, cumulativeLikes
                FROM	wcf" . WCF_N . "_like_object
                " . $conditions;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditions->getParameters());
        $cumulativeLikes = $statement->fetchMap('objectID', 'cumulativeLikes');

        foreach ($this->objectList as $todo) {
            $editor = new TodoEditor($todo);
            $data = [];

            // update cumulative likes
            $data['cumulativeLikes'] = isset($cumulativeLikes[$todo->todoID]) ? $cumulativeLikes[$todo->todoID] : 0;

            // update description
            $this->getHtmlInputProcessor()->reprocess($todo->message, 'de.julian-pfeil.todolist.todo', $todo->todoID);
            $data['message'] = $this->getHtmlInputProcessor()->getHtml();
            if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->getHtmlInputProcessor())) {
                $data['hasEmbeddedObjects'] = 1;
            } else {
                $data['hasEmbeddedObjects'] = 0;
            }

            $editor->update($data);
            $description = $todo->getPlainMessage();
            if (mb_strlen($description) > 10000000) {
                $description = substr($description, 0, 10000000);
            }

            SearchIndexManager::getInstance()->set('de.julian-pfeil.todolist.todo', $todo->todoID, $description, $todo->subject, $todo->time, $todo->userID, $todo->username, $todo->languageID);
        }
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
