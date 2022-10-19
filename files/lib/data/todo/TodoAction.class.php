<?php

namespace todolist\data\todo;

use todolist\system\user\notification\object\TodoUserNotificationObject;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF;
use wcf\system\language\LanguageFactory;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\util\UserUtil;
use wcf\system\request\LinkHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\like\LikeHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;

/**
 * Executes todo-related actions.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage data.todo
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

        if (empty($this->object)) {
            $this->setObjects([$todo]);
        }

        $todoEditor = new TodoEditor($todo);

        // fire activity event
        $languageID = (!isset($this->parameters['data']['languageID']) || ($this->parameters['data']['languageID'] === null)) ? LanguageFactory::getInstance()->getDefaultLanguageID() : $this->parameters['data']['languageID'];
        UserActivityEventHandler::getInstance()->fireEvent('de.julian-pfeil.todolist.recentActivityEvent.todo', $todo->todoID, $languageID, $todo->userID, $todo->time);

        $this->setSearchIndex($todo);

        // save embedded objects
        $this->saveEmbeddedObjects($todoEditor, $todo);

        // update watched objects
        $category = $todo->getCategory();
        UserObjectWatchHandler::getInstance()->updateObject(
            'de.julian-pfeil.todolist.todo.category',
            $category->categoryID,
            'category',
            'de.julian-pfeil.todolist.todo',
            new TodoUserNotificationObject($todo)
        );

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
        foreach ($this->getObjects() as $todoEditor) {
            $objectIDs[] = $todoEditor->todoID;

            $todo = new Todo($todoEditor->todoID);

            $this->setSearchIndex($todo);

            $this->saveEmbeddedObjects($todoEditor, $todo);
        }
    }

    public function loadDescriptionHtmlInputProcessor($descriptionHtmlInputProcessor)
    {
        if (!empty($descriptionHtmlInputProcessor)) {
            /** @var HtmlInputProcessor $htmlInputProcessor */
            $htmlInputProcessor = $descriptionHtmlInputProcessor;
            return $htmlInputProcessor->getHtml();
        } else {
            return $this->parameters['data']['description'];
        }
    }

    public function saveEmbeddedObjects(TodoEditor $todoEditor, Todo $todo)
    {
        if (!empty($this->parameters['description_htmlInputProcessor'])) {
            $this->parameters['description_htmlInputProcessor']->setObjectID($todo->todoID);

            if ($todo->hasEmbeddedObjects != MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['description_htmlInputProcessor'])) {
                $todoEditor->update([
                    'hasEmbeddedObjects' => $todo->hasEmbeddedObjects ? 0 : 1
                ]);
            }
        }
    }

    public function setSearchIndex(Todo $todo)
    {
        $description = $todo->getPlainMessage();

        if (mb_strlen($description) > 10000000) {
            $description = substr($description, 0, 10000000);
        }

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


    /**
     * Loads todos for given object ids.
     */
    protected function loadTodos()
    {
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
    public function delete()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        $todoIDs = $todoData = [];
        foreach ($this->getObjects() as $todo) {
            $todoIDs[] = $todo->todoID;


            $todoData[$todo->todoID] = $todo->userID;
        }

        // remove user activity events
        UserActivityEventHandler::getInstance()->removeEvents('de.julian-pfeil.todolist.recentActivityEvent.todo', $todoIDs);

        foreach ($this->getObjects() as $todo) {
            $todoIDs[] = $todo->todoID;

            $todo->delete();

            $this->addTodoData($todo, 'deleted', LinkHandler::getInstance()->getLink('TodoList', ['application' => 'todolist']));
        }

        if (!empty($todoIDs)) {
            // delete like data
            LikeHandler::getInstance()->removeLikes('de.julian-pfeil.todolist.likeableTodo', $todoIDs);

            // delete todo from search index
            SearchIndexManager::getInstance()->delete('de.julian-pfeil.todolist.todo', $todoIDs);

            // delete embedded objects
            MessageEmbeddedObjectManager::getInstance()->removeObjects('de.julian-pfeil.todolist.todo', $todoIDs);
        }

        return $this->getTodoData();
    }

    /**
     * Validates parameters to mark todos as done.
     */
    public function validateMarkAsDone()
    {
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
    public function markAsDone()
    {
        foreach ($this->getObjects() as $todoEditor) {
            $todoEditor->update(['isDone' => 1]);

            $todo = $todoEditor->getDecoratedObject();

            if (WCF::getUser()->userID != $todo->userID) {
                $recipientIDs = [$todo->userID];
                UserNotificationHandler::getInstance()->fireEvent(
                    'todo', // event name
                    'de.julian-pfeil.todolist.todo', // event object type name
                    new TodoUserNotificationObject(new Todo($todo->todoID)),
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
    public function validateMarkAsUndone()
    {
        $this->validateMarkAsDone();
    }

    /**
     * Marks todos as undone.
     */
    public function markAsUndone()
    {
        foreach ($this->getObjects() as $todoEditor) {
            $todoEditor->update(['isDone' => 0]);

            $todo = $todoEditor->getDecoratedObject();

            if (WCF::getUser()->userID != $todo->userID) {
                $recipientIDs = [$todo->userID];
                UserNotificationHandler::getInstance()->fireEvent(
                    'todo', // event name
                    'de.julian-pfeil.todolist.todo', // event object type name
                    new TodoUserNotificationObject(new Todo($todo->todoID)),
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
    protected function addTodoData(Todo $todo, $key, $value)
    {
        if (!isset($this->todoData[$todo->todoID])) {
            $this->todoData[$todo->todoID] = [];
        }

        $this->todoData[$todo->todoID][$key] = $value;
    }

    /**
     * Returns stored todo data.
     */
    protected function getTodoData()
    {
        return [
            'todoData' => $this->todoData
        ];
    }

    /**
     * Sends todo-edit user notification
     */
    public function sendNotification()
    {
        if (WCF::getUser()->userID != $this->formObject->userID) {
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
