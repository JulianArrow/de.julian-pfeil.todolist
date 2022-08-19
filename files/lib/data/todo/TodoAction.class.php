<?php

namespace todolist\data\todo;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF;
use wcf\util\UserUtil;
use wcf\system\request\LinkHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;

/**
 * Executes todo-related actions.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package WoltLabSuite\Core\Data\Todo
 *
 * @method  Todo      create()
 * @method  TodoEditor[]  getObjects()
 * @method  TodoEditor    getSingleObject()
 */
class TodoAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
	public function create()
    {
        if (!isset($this->parameters['data']['creationDate'])) {
            $this->parameters['data']['creationDate'] = TIME_NOW;
        }
        if (!isset($this->parameters['data']['userID'])) {
            $this->parameters['data']['userID'] = WCF::getUser()->userID;
            $this->parameters['data']['username'] = WCF::getUser()->username;
        }

        if (LOG_IP_ADDRESS) {
            if (!isset($this->parameters['data']['ipAddress'])) {
                $this->parameters['data']['ipAddress'] = UserUtil::getIpAddress();
            }
        } else {
            unset($this->parameters['data']['ipAddress']);
        }

        $object = parent::create();

        return $object;
    }
    
	/**
	 * Loads todos for given object ids.
	 */
	protected function loadTodos() {
		if (empty($this->objects)) {
            $this->readObjects();

            if (empty($this->objects)) {
                throw new UserInputException('objectIDs');
            }
        }
	}
	
    /**
     * @inheritDoc
     */
    public function validateDelete()
    {
        $this->loadTodos();

        foreach ($this->getObjects() as $todoEditor) {
            if (!$todoEditor->canDelete()) {
                throw new PermissionDeniedException();
            }
        }
    }

    
	
	/**
	 * Deletes given todos.
	 */
	public function delete() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$todoIDs = $todoData = [];
		foreach ($this->getObjects() as $todo) {
			$todoIDs[] = $todo->todoID;
		}
		
		// remove todos
		foreach ($this->getObjects() as $todo) {
			$todo->delete();
			
			$this->addTodoData($todo->getDecoratedObject(), 'deleted', LinkHandler::getInstance()->getLink('TodoList', ['application' => 'todolist']));
		}
		
		return $this->getTodoData();
	}

    /**
	 * Validates parameters to mark todos as done.
	 */
	public function validateMarkAsDone() {
		$this->loadTodos();
		
		
        foreach ($this->getObjects() as $todoEditor) {
            if (!$todoEditor->canEdit()) {
                throw new PermissionDeniedException();
            }
        }
	}
	
	/**
	 * Marks todos as done.
	 */
	public function markAsDone() {
		foreach ($this->getObjects() as $todoEditor) {
			$todoEditor->update(['done' => 1]);
			
			$this->addTodoData($todoEditor->getDecoratedObject(), 'done', 1);
		}
		
		return $this->getTodoData();
	}
	
	/**
	 * Validates parameters to mark todos as undone.
	 */
	public function validateMarkAsUndone() {
		$this->validateMarkAsDone();
	}
	
	/**
	 * Marks todos as undone.
	 */
	public function markAsUndone() {
		foreach ($this->getObjects() as $todoEditor) {
			$todoEditor->update(['done' => 0]);
			
			$this->addTodoData($todoEditor->getDecoratedObject(), 'done', 0);
		}
		
		return $this->getTodoData();
	}
	
	/**
	 * Adds todo data.
	 */
	protected function addTodoData(Todo $todo, $key, $value) {
		if (!isset($this->todoData[$todo->todoID])) {
			$this->todoData[$todo->todoID] = [];
		}
		
		$this->todoData[$todo->todoID][$key] = $value;
	}
	
	/**
	 * Returns stored todo data.
	 */
	protected function getTodoData() {
		return [
			'todoData' => $this->todoData
		];
	}
}
