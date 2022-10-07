<?php
namespace todolist\system\worker;

use todolist\data\todo\TodoEditor;
use todolist\data\todo\TodoList;

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
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */

class TodolistRebuildDataWorker extends AbstractRebuildDataWorker {
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
	public function execute() {
		parent::execute();
		
		if (!$this->loopCount) {
			// reset search index
			SearchIndexManager::getInstance()->reset('de.julian-pfeil.todolist.todo');
		}
		
		if (!count($this->objectList)) {
			return;
		}
		
		// get label status
		$hasLabels = [];
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('objectTypeID = ?', [ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.label.object', 'de.julian-pfeil.todolist.todo')->objectTypeID]);
		$conditionBuilder->add('objectID IN (?)', [$this->objectList->getObjectIDs()]);
		$sql = "SELECT	DISTINCT objectID
				FROM	wcf".WCF_N."_label_object
				".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		while ($objectID = $statement->fetchColumn()) {
			$hasLabels[$objectID] = 1;
		}
		
		// fetch cumulative likes
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID = ?", [ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.like.likeableObject', 'com.uz.todolist.likeableTodo')]);
		$conditions->add("objectID IN (?)", [$this->objectList->getObjectIDs()]);
		
		$sql = "SELECT	objectID, cumulativeLikes
				FROM	wcf".WCF_N."_like_object
				".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$cumulativeLikes = $statement->fetchMap('objectID', 'cumulativeLikes');
		
		// prepare statements$commentObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.comment.commentableContent', 'de.julian-pfeil.todolist.todoComment');
		$sql = "SELECT	COUNT(*) AS comments, SUM(responses) AS responses
				FROM	wcf".WCF_N."_comment
				WHERE	objectTypeID = ? AND objectID = ?";
		$commentStatement = WCF::getDB()->prepareStatement($sql);
		
		$todoIDs = $itemsToUser = [];
		WCF::getDB()->beginTransaction();
		foreach ($this->objectList as $todo) {
			$editor = new TodoEditor($todo);
			$data = [];
			
			// count comments
			$commentStatement->execute([$commentObjectType->objectTypeID, $todo->todoID]);
			$row = $commentStatement->fetchSingleRow();
			$data['comments'] = $row['comments'] + $row['responses'];
			
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
			
			// update label status
			if (isset($hasLabels[$todo->todoID])) {
				$data['hasLabels'] = 1;
			}
			
			$editor->update($data);

            $description = $todo->getPlainMessage();
		
			if (mb_strlen($description) > 10000000) $description = substr($description, 0, 10000000);
			
			SearchIndexManager::getInstance()->set(
				'de.julian-pfeil.todolist.todo',
				$todo->todoID,
				$description,
				$todo->subject,
				$todo->time,
				$todo->userID,
				$todo->username,
				$todo->languageID
			);
			
			$todoIDs[] = $todo->todoID;
		}
		
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * @return HtmlInputProcessor
	 */
	protected function getHtmlInputProcessor() {
		if ($this->htmlInputProcessor === null) {
			$this->htmlInputProcessor = new HtmlInputProcessor();
		}
		
		return $this->htmlInputProcessor;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'todo.todoID';
	}
}
