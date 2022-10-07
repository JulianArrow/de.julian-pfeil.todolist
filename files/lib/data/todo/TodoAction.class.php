<?php

namespace todolist\data\todo;

use todolist\system\log\modification\TodoModificationLogHandler;
use todolist\system\user\notification\object\TodoUserNotificationObject;
use todolist\system\label\object\TodoLabelObjectHandler;
use todolist\data\category\TodoCategory;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF;
use wcf\data\label\Label;
use wcf\system\language\LanguageFactory;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\util\UserUtil;
use wcf\system\tagging\TagEngine;
use wcf\system\request\LinkHandler;
use wcf\system\label\LabelHandler;
use wcf\system\like\LikeHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\user\notification\UserNotificationHandler;

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
        if (!isset($this->parameters['data']['time'])) {
            $this->parameters['data']['time'] = TIME_NOW;
        }
        $this->parameters['data']['lastEditTime'] = TIME_NOW;

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

        $this->parameters['data']['description'] = $this->loadDescriptionHtmlInputProcessor($this->parameters['description_htmlInputProcessor']);
		
		// create todo
		$todo = parent::create();

		$todoEditor = new TodoEditor($todo);
        
        $this->setSearchIndex($todo);
        
        // save embedded objects
		$this->saveEmbeddedObjects($todoEditor, $todo);

		if (defined('TODOLIST_TAGGING_PLUGIN')) {
			// save tags
			if (!empty($this->parameters['tags'])) {
				$this->saveTags($todo);
			}
		}

        return $todo;
    }
	
	/**
     * @inheritDoc
     */
    public function update()
    {
        // last change
        $this->parameters['data']['lastEditTime'] = TIME_NOW;

        $this->parameters['data']['description'] = $this->loadDescriptionHtmlInputProcessor($this->parameters['description_htmlInputProcessor']);
        
        parent::update();

        // get ids
		$objectIDs = [];
		foreach ($this->getObjects() as $todo) {
			$objectIDs[] = $todo->todoID;

            $todoEditor = new TodoEditor($todo);
            
            $this->setSearchIndex($todo);

			// add log todo
			TodoModificationLogHandler::getInstance()->edit($todo->getDecoratedObject(), (isset($this->parameters['editReason']) ? $this->parameters['editReason'] : ''));     
            
            // save embedded objects
            $this->saveEmbeddedObject($todoEditor, $todo);


            
		if (defined('TODOLIST_TAGGING_PLUGIN')) {
			// save tags
			$this->saveTags($todo);
		}
        }
    }

    public function loadDescriptionHtmlInputProcessor($descriptionHtmlInputProcessor) {
        if (!empty($descriptionHtmlInputProcessor)) {
            /** @var HtmlInputProcessor $htmlInputProcessor */
            $htmlInputProcessor = $descriptionHtmlInputProcessor;
            return $htmlInputProcessor->getHtml();
        } else {
            return $this->parameters['data']['description'];
        }
    }

    public function saveEmbeddedObjects(TodoEditor $todoEditor, Todo $todo) {
        if (!empty($this->parameters['htmlInputProcessor'])) {
			$this->parameters['htmlInputProcessor']->setObjectID($todo->todoID);

            if ($todo->hasEmbeddedObjects != MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor'])) {
                $todoEditor->update([
                    'hasEmbeddedObjects' => $todo->hasEmbeddedObjects ? 0 : 1
                ]);
            }
		}
    }

    public function setSearchIndex(Todo $todo) {
        $description = $todo->getPlainMessage();

        if (mb_strlen($description) > 10000000) $description = substr($description, 0, 10000000);

        SearchIndexManager::getInstance()->set(
			'de.julian-pfeil.todolist.todo',
			$todo->todoID,
			$description,
			$todo->getTitle(),
			$todo->time,
			$todo->userID,
			$todo->username
		);
    }

    public function saveTags(Todo $todo) {
        if (isset($this->parameters['tags']) ) {
            // set language id (cannot be zero)
            $languageID = (!isset($this->parameters['data']['languageID']) || ($this->parameters['data']['languageID'] === null)) ? LanguageFactory::getInstance()->getDefaultLanguageID() : $this->parameters['data']['languageID'];
            
            TagEngine::getInstance()->addObjectTags('de.julian-pfeil.todolist.todo', $todo->todoID, $this->parameters['tags'], $languageID);
        }
    }
    
    /**
	 * Validates 'assignLabel' action.
	 */
	public function validateAssignLabel() {
		if (!defined('TODOLIST_LABELS_PLUGIN')) {
			throw new PermissionDeniedException();
		}

		$this->readInteger('categoryID');
		
		$this->category = TodoCategory::getCategory($this->parameters['categoryID']);
		if ($this->category === null) {
			throw new UserInputException('category');
		}
		
		if (!$this->category->canView()) {
			throw new PermissionDeniedException();
		}
		
		// validate todos
		$this->readObjects();
		if (empty($this->objects)) {
			throw new UserInputException('objectIDs');
		}
		
		// reload todos with assigned categories
		$todoList = new TodoList();
		$todoList->decoratorClassName = TodoEditor::class;
		$todoList->setObjectIDs($this->objectIDs);
		$todoList->readObjects();
		$this->objects = $todoList->getObjects();
		
		foreach ($this->getObjects() as $todo) {
			if ($this->category->categoryID != $todo->categoryID) {
				throw new UserInputException('objectIDs');
			}
		}
		
		// validate label ids
		$this->parameters['labelIDs'] = empty($this->parameters['labelIDs']) ? [] : ArrayUtil::toIntegerArray($this->parameters['labelIDs']);
		if (!empty($this->parameters['labelIDs'])) {
			$labelGroups = $this->category->getLabelGroups();
			if (empty($labelGroups)) {
				throw new PermissionDeniedException();
			}
			
			foreach ($this->parameters['labelIDs'] as $groupID => $labelID) {
				if (!isset($labelGroups[$groupID]) || !$labelGroups[$groupID]->isValid($labelID)) {
					throw new UserInputException('labelIDs');
				}
			}
		}
	}

    /**
	 * Assigns labels to todos and returns the updated list.
	 */
	public function assignLabel() {
		$objectTypeID = LabelHandler::getInstance()->getObjectType('de.julian-pfeil.todolist.todo')->objectTypeID;
		$todoIDs = [];
		foreach ($this->getObjects() as $todo) {
			$todoIDs[] = $todo->todoID;
		}
		
		// fetch old labels for modification log creation
		$oldLabels = LabelHandler::getInstance()->getAssignedLabels($objectTypeID, $todoIDs);
		
		foreach ($this->getObjects() as $todo) {
			LabelHandler::getInstance()->setLabels($this->parameters['labelIDs'], $objectTypeID, $todo->todoID);
			
			// update hasLabels flag
			$todo->update(['hasLabels' => !empty($this->parameters['labelIDs']) ? 1 : 0]);
		}
		
		$assignedLabels = LabelHandler::getInstance()->getAssignedLabels($objectTypeID, $todoIDs);
		
		$labels = [];
		if (!empty($assignedLabels)) {
			$tmp = [];
			
			// get labels from first object
			$labelList = reset($assignedLabels);
			
			// log adding new labels
			WCF::getDB()->beginTransaction();
			foreach ($this->getObjects() as $todo) {
				$newLabels = $labelList;
				if (!empty($oldLabels[$todo->todoID])) {
					$newLabels = array_diff_key($labelList, $oldLabels[$todo->todoID]);
				}
				
				foreach ($newLabels as $label) {
					TodoModificationLogHandler::getInstance()->setLabel($todo->getDecoratedObject(), $label);
				}
			}
			WCF::getDB()->commitTransaction();
			
			foreach ($labelList as $label) {
				$tmp[$label->labelID] = [
						'cssClassName' => $label->cssClassName,
						'label' => $label->getTitle(),
						'link' => LinkHandler::getInstance()->getLink('TodoList', ['application' => 'todolist', 'object' => $this->category], 'labelIDs['.$label->groupID.']='.$label->labelID)
				];
			}
			
			// sort labels by label group show order
			$labelGroups = TodoLabelObjectHandler::getInstance()->getLabelGroups();
			foreach ($labelGroups as $labelGroup) {
				foreach ($tmp as $labelID => $labelData) {
					if ($labelGroup->isValid($labelID)) {
						$labels[] = $labelData;
						break;
					}
				}
			}
		}
		
		return ['labels' => $labels];
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

        foreach ($this->getObjects() as $todo) {
            if (!$todo->canDelete()) {
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
		
		$todoIDs = $todoData = $userCounters = [];
		foreach ($this->getObjects() as $todo) {
			$todoIDs[] = $todo->todoID;
			
			
            $todoData[$todo->todoID] = $todo->userID;
		}
		
		// remove user activity events
		if (!empty($todoData)) {
			$this->removeActivityEvents($todoData);
		}
		
		foreach ($this->getObjects() as $todo) {
			$todoIDs[] = $todo->todoID;
		
			$todo->delete();
			
			$this->addTodoData($todo->getDecoratedObject(), 'deleted', LinkHandler::getInstance()->getLink('TodoList', ['application' => 'todolist']));
		
            TodoModificationLogHandler::getInstance()->delete($todo->getDecoratedObject());
        }
        
        if (!empty($todoIDs)) {
            // delete like data
			LikeHandler::getInstance()->removeLikes('de.julian-pfeil.todolist.likeableTodo', $todoIDs);
			
			// delete comments
			CommentHandler::getInstance()->deleteObjects('de.julian-pfeil.todolist.todoComment', $todoIDs);
            
            // delete tag to object entries
			TagEngine::getInstance()->deleteObjects('de.julian-pfeil.todolist.todo', $todoIDs);
			
			// delete todo from search index
			SearchIndexManager::getInstance()->delete('de.julian-pfeil.todolist.todo', $todoIDs);
			
			// delete embedded objects
			MessageEmbeddedObjectManager::getInstance()->removeObjects('de.julian-pfeil.todolist.todo', $todoIDs);
			
			// delete the log entries except for deleting the todo
			TodoModificationLogHandler::getInstance()->deleteLogs($todoIDs, ['delete']);
		}

		// delete label assignments
		LabelHandler::getInstance()->removeLabels(LabelHandler::getInstance()->getObjectType('de.julian-pfeil.todolist.todo')->objectTypeID, $todoIDs);
		
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
			$todoEditor->update(['isDone' => 1]);
			
            if (WCF::getUser()->userID != $todoEditor->getDecoratedObject()->userID)
            {
                $recipientIDs = [$todoEditor->getDecoratedObject()->userID];
                UserNotificationHandler::getInstance()->fireEvent(
                    'todo', // event name
                    'de.julian-pfeil.todolist.todo', // event object type name
                    new TodoUserNotificationObject(new Todo($todoEditor->getDecoratedObject()->todoID)),
                    $recipientIDs
                );
            }

			$this->addTodoData($todoEditor->getDecoratedObject(), 'isDone', 1);
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
			$todoEditor->update(['isDone' => 0]);

            if (WCF::getUser()->userID != $todoEditor->getDecoratedObject()->userID)
            {
                $recipientIDs = [$todoEditor->getDecoratedObject()->userID];
                UserNotificationHandler::getInstance()->fireEvent(
                    'todo', // event name
                    'de.julian-pfeil.todolist.todo', // event object type name
                    new TodoUserNotificationObject(new Todo($todoEditor->getDecoratedObject()->todoID)),
                    $recipientIDs
                );
            }
			
			$this->addTodoData($todoEditor->getDecoratedObject(), 'isDone', 0);
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

	/**
     * Sends todo-edit user notification
     */
    public function sendNotification()
    {
        if (WCF::getUser()->userID != $this->formObject->userID)
        {

            $recipientIDs = [$this->formObject->userID];
            UserNotificationHandler::getInstance()->fireEvent(
                'todo', // event name
                'de.julian-pfeil.todolist.todo', // event object type name
                new TodoUserNotificationObject(new Todo($this->formObject->todoID)),
                $recipientIDs
            );
        }
    }
}
