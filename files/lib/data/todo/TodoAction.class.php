<?php

namespace todolist\data\todo;

use todolist\system\user\notification\object\TodoUserNotificationObject;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\user\UserProfileList;
use wcf\system\comment\CommentHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\reaction\ReactionHandler;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\MessageUtil;
use wcf\util\UserUtil;

/**
 * Executes todo-related actions.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
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

        $todoEditor = new TodoEditor($todo);

        if (empty($this->object)) {
            $this->setObjects([$todoEditor]);
        }

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
     * Triggers the publication of to-do.
     */
    public function triggerPublication()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        $htmlInputProcessor = null;

        $activityEvents = [];
        foreach ($this->getObjects() as $todo) {
            // send notification for current editor
            $recipientIDs = [];
            if ($todo->currentEditor !== null) {
                $recipientIDs[] = $todo->currentEditor;
            }

            if (!empty($recipientIDs)) {
                UserNotificationHandler::getInstance()->fireEvent(
                    'editor',
                    'de.julian-pfeil.todolist.todo',
                    new TodoUserNotificationObject($todo->getDecoratedObject()),
                    $recipientIDs
                );
            }

            // send notifications for quotes and mentions
            if ($htmlInputProcessor === null) {
                $htmlInputProcessor = new HtmlInputProcessor();
            }
            $htmlInputProcessor->processIntermediate($todo->description);

            $usernames = MessageUtil::getQuotedUsers($htmlInputProcessor);
            if (!empty($usernames)) {
                // get user profiles
                $userList = new UserProfileList();
                $userList->getConditionBuilder()->add('user_table.username IN (?)', [$usernames]);
                if ($todo->userID) {
                    // ignore self-quoting
                    $userList->getConditionBuilder()->add('user_table.userID <> ?', [$todo->userID]);
                }
                $userList->readObjects();
                $recipientIDs = [];
                foreach ($userList as $userProfile) {
                    $user = $userProfile->getDecoratedObject();
                    if ($todo->category->canView($user)) {
                        $recipientIDs[] = $user->userID;
                    }
                }

                // fire event
                if (!empty($recipientIDs)) {
                    UserNotificationHandler::getInstance()->fireEvent(
                        'quote',
                        'de.julian-pfeil.todolist.todo',
                        new TodoUserNotificationObject($todo->getDecoratedObject()),
                        $recipientIDs
                    );
                }
            }

            // check for mentions
            $userIDs = MessageUtil::getMentionedUserIDs($htmlInputProcessor);
            if (!empty($userIDs)) {
                // get user profiles
                $userList = new UserProfileList();
                $userList->getConditionBuilder()->add('user_table.userID IN (?)', [$userIDs]);
                if ($todo->userID) {
                    $userList->getConditionBuilder()->add('user_table.userID <> ?', [$todo->userID]);
                }
                $userList->readObjects();
                $recipientIDs = [];
                foreach ($userList as $userProfile) {
                    $user = $userProfile->getDecoratedObject();
                    if ($todo->category->canView($user)) {
                        $recipientIDs[] = $user->userID;
                    }
                }

                // fire event
                if (!empty($recipientIDs)) {
                    UserNotificationHandler::getInstance()->fireEvent(
                        'mention',
                        'de.julian-pfeil.todolist.todo',
                        new TodoUserNotificationObject($todo->getDecoratedObject()),
                        $recipientIDs
                    );
                }
            }
        }

        if (!empty($activityEvents)) {
            // fire activity event
            $languageID = LanguageFactory::getInstance()->getDefaultLanguageID();
            UserActivityEventHandler::getInstance()->fireEvent('de.julian-pfeil.todolist.recentActivityEvent.todo', $todo->todoID, $languageID, $todo->userID, $todo->time);
        }
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        // last change
        $this->parameters['data']['lastEditTime'] = TIME_NOW;

        $this->parameters['data']['description'] = $this->loadDescriptionHtmlInputProcessor($this->parameters['description_htmlInputProcessor']);
        
        foreach ($this->getObjects() as $todoEditor) {
            if (\intval($this->parameters['data']['currentEditor']) != $todoEditor->currentEditor && \intval($this->parameters['data']['currentEditor']) > 0) {
                // send notification for current editor
                $recipientIDs = [];
                if ($todo->currentEditor !== null) {
                    $recipientIDs[] = $todo->currentEditor;
                }

                if (!empty($recipientIDs)) {
                    UserNotificationHandler::getInstance()->fireEvent(
                        'editor',
                        'de.julian-pfeil.todolist.todo',
                        new TodoUserNotificationObject($todo->getDecoratedObject()),
                        $recipientIDs
                    );
                }
            }
        }

        parent::update();

        // get ids
        $objectIDs = [];
        foreach ($this->getObjects() as $todoEditor) {
            $objectIDs[] = $todoEditor->todoID;

            $todo = new Todo($todoEditor->todoID);

            // notification for author & non-author edits
            $this->sendEditNotification($todo);

            $this->setSearchIndex($todo);

            $this->saveEmbeddedObjects($todoEditor, $todo);

            // handle new mentions / quotes
            if (!empty($this->parameters['description_htmlInputProcessor'])) {
                $quotedUsernames = MessageUtil::getQuotedUsers($this->parameters['description_htmlInputProcessor']);
                $mentionedUserIDs = MessageUtil::getMentionedUserIDs($this->parameters['description_htmlInputProcessor']);

                if (!empty($quotedUsernames) || !empty($mentionedUserIDs)) {
                    // process old message
                    $htmlInputProcessor = new HtmlInputProcessor();
                    $htmlInputProcessor->processIntermediate($todo->message);

                    // Reload todo to get the updated message.
                    $todo = new Todo($todo->todoID);

                    if (!empty($quotedUsernames)) {
                        // find users that have not been quoted in this todo before
                        $existingUsernames = \array_map(
                            'mb_strtolower',
                            MessageUtil::getQuotedUsers($htmlInputProcessor)
                        );
                        $quotedUsernames = \array_unique(\array_filter(
                            $quotedUsernames,
                            static function ($username) use ($existingUsernames) {
                                return !\in_array(\mb_strtolower($username), $existingUsernames);
                            }
                        ));

                        if (!empty($quotedUsernames)) {
                            // get user profiles
                            $userList = new UserProfileList();
                            $userList->getConditionBuilder()->add('user_table.username IN (?)', [$quotedUsernames]);
                            if ($todo->userID) {
                                $userList->getConditionBuilder()->add('user_table.userID <> ?', [$todo->userID]);
                            }
                            $userList->readObjects();
                            $recipientIDs = [];
                            
                            foreach ($userList as $userProfile) {
                                $user = $userProfile->getDecoratedObject();
                                if ($todo->category->canView($user)) {
                                    $recipientIDs[] = $user->userID;
                                }
                            }

                            // fire event
                            if (!empty($recipientIDs)) {
                                UserNotificationHandler::getInstance()->fireEvent(
                                    'quote',
                                    'de.julian-pfeil.todolist.todo',
                                    new TodoUserNotificationObject($todo),
                                    $recipientIDs
                                );
                            }
                        }
                    }

                    if (!empty($mentionedUserIDs)) {
                        // find users that have not been mentioned in this todo before
                        $existingUserIDs = MessageUtil::getMentionedUserIDs($htmlInputProcessor);
                        $mentionedUserIDs = \array_unique(\array_filter(
                            $mentionedUserIDs,
                            static function ($userID) use ($existingUserIDs) {
                                return !\in_array($userID, $existingUserIDs);
                            }
                        ));

                        if (!empty($mentionedUserIDs)) {
                            // get user profiles
                            $userList = new UserProfileList();
                            $userList->getConditionBuilder()->add('user_table.userID IN (?)', [$mentionedUserIDs]);
                            if ($todo->userID) {
                                $userList->getConditionBuilder()->add('user_table.userID <> ?', [$todo->userID]);
                            }

                            $userList->readObjects();
                            $recipientIDs = [];
                            
                            foreach ($userList as $userProfile) {
                                $user = $userProfile->getDecoratedObject();
                                if ($todo->category->canView($user)) {
                                    $recipientIDs[] = $user->userID;
                                }
                            }

                            // fire event
                            if (!empty($recipientIDs)) {
                                UserNotificationHandler::getInstance()->fireEvent(
                                    'mention',
                                    'de.julian-pfeil.todolist.todo',
                                    new TodoUserNotificationObject($todo),
                                    $recipientIDs
                                );
                            }
                        }
                    }
                }
            }
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
                    'hasEmbeddedObjects' => $todo->hasEmbeddedObjects ? 0 : 1,
                ]);
            }
        }
    }

    public function setSearchIndex(Todo $todo)
    {
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

    public function sendEditNotification($todo)
    {
        // author notification when edited
        if (WCF::getUser()->userID != $todo->userID) {
            UserNotificationHandler::getInstance()->fireEvent(
                'edit', // event name
                'de.julian-pfeil.todolist.todo', // event object type name
                new TodoUserNotificationObject(new Todo($todo->todoID)),
                [$todo->userID] //recipient
            );
        }

        // watched objects
        UserObjectWatchHandler::getInstance()->updateObject(
            'de.julian-pfeil.todolist.todo',
            $todo->todoID,
            'todo',
            'de.julian-pfeil.todolist.todo',
            new TodoUserNotificationObject(new Todo($todo->todoID))
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

        $todoIDs = [];
        foreach ($this->getObjects() as $todoEditor) {
            $todoIDs[] = $todoEditor->todoID;

            $todoEditor->delete();

            $this->addTodoData($todoEditor->getDecoratedObject(), 'deleted', LinkHandler::getInstance()->getLink('TodoList', ['application' => 'todolist']));
        }

        if (!empty($todoIDs)) {
            // delete comments
            CommentHandler::getInstance()->deleteObjects('de.julian-pfeil.todolist.todoComment', $todoIDs);

            // delete like data
            ReactionHandler::getInstance()->removeReactions('de.julian-pfeil.todolist.likeableTodo', $todoIDs);

            // delete todo from search index
            SearchIndexManager::getInstance()->delete('de.julian-pfeil.todolist.todo', $todoIDs);

            // delete embedded objects
            MessageEmbeddedObjectManager::getInstance()->removeObjects('de.julian-pfeil.todolist.todo', $todoIDs);

            // remove user activity events
            UserActivityEventHandler::getInstance()->removeEvents('de.julian-pfeil.todolist.recentActivityEvent.todo', $todoIDs);

            // delete todo notifications
            UserNotificationHandler::getInstance()->markAsConfirmed('todo', 'de.julian-pfeil.todolist.todo', [], $todoIDs);
            UserNotificationHandler::getInstance()->markAsConfirmed('edit', 'de.julian-pfeil.todolist.todo', [], $todoIDs);
            UserNotificationHandler::getInstance()->markAsConfirmed('quote', 'de.julian-pfeil.todolist.todo', [], $todoIDs);
            UserNotificationHandler::getInstance()->markAsConfirmed('mention', 'de.julian-pfeil.todolist.todo', [], $todoIDs);

            // delete subscriptions
            UserObjectWatchHandler::getInstance()->deleteObjects('de.julian-pfeil.todolist.todo', $todoIDs);

            // delete comment notifications
            UserNotificationHandler::getInstance()->markAsConfirmed('comment', 'de.julian-pfeil.todolist.todoComment.notification', [], $todoIDs);
            UserNotificationHandler::getInstance()->markAsConfirmed('commentResponse', 'de.julian-pfeil.todolist.todoComment.response.notification', [], $todoIDs);
            UserNotificationHandler::getInstance()->markAsConfirmed('commentResponseOwner', 'de.julian-pfeil.todolist.todoComment.response.notification', [], $todoIDs);
            UserNotificationHandler::getInstance()->markAsConfirmed('like', 'de.julian-pfeil.todolist.todoComment.like.notification', [], $todoIDs);
            UserNotificationHandler::getInstance()->markAsConfirmed('like', 'de.julian-pfeil.todolist.todoComment.response.like.notification', [], $todoIDs);
        }

        //reset user storage
        UserStorageHandler::getInstance()->resetAll(Todo::USER_STORAGE_SUBSCRIBED_TODOS);

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

            $this->sendEditNotification($todo);

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

            $this->sendEditNotification($todo);

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
            'todoData' => $this->todoData,
        ];
    }
}
