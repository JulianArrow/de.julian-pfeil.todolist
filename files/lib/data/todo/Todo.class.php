<?php

namespace todolist\data\todo;

use todolist\data\todo\category\TodoCategory;
use todolist\page\TodoPage;
use wcf\data\DatabaseObject;
use wcf\data\ITitledLinkObject;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\LinkHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a todo.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage data.todo
 */
class Todo extends DatabaseObject implements ITitledLinkObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'todo';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'todoID';

    /**
     * todos category
     * @var Category
     */
    public $category;

    /**
     * true if embedded objects have already been loaded
     */
    protected $embeddedObjectsLoaded = false;

    /**
     * subscribed todos field name
     */
    public const USER_STORAGE_SUBSCRIBED_TODOS = self::class . "\0subscribedTodos";

    /**
     * ids of subscribed todos
     */
    protected static $subscribedTodos;

    /**
     * @inheritDoc
     */
    public function __construct($id, ?array $row = null, ?self $object = null)
    {
        parent::__construct($id, $row, $object);

        $this->getCategory();
    }

    /**
     * Returns the name of the todo if a todo object is treated as a string.
     *
     * @return  string
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * Returns the todo object with the given id.
     */
    public static function getTodo($todoID)
    {
        return new self($todoID);
    }

    /**
     * @inheritdoc
     */
    public function getObjectID()
    {
        return $this->todoID;
    }

    /**
     * Loads the embedded objects.
     */
    public function loadEmbeddedObjects()
    {
        if (!$this->embeddedObjectsLoaded) {
            MessageEmbeddedObjectManager::getInstance()->setActiveMessage('de.julian-pfeil.todolist.todo.content', $this->todoID);

            if ($this->hasEmbeddedObjects) {
                MessageEmbeddedObjectManager::getInstance()->loadObjects('de.julian-pfeil.todolist.todo.content', [$this->todoID]);
            }

            $this->embeddedObjectsLoaded = true;
        }
    }

    /**
     * Returns subscribed todo IDs.
     */
    public static function getSubscribedTodoIDs()
    {
        if (self::$subscribedTodos === null) {
            self::$subscribedTodos = [];

            if (WCF::getUser()->userID) {
                $data = UserStorageHandler::getInstance()->getField(self::USER_STORAGE_SUBSCRIBED_TODOS);

                // cache does not exist or is outdated
                if ($data === null) {
                    $objectTypeID = UserObjectWatchHandler::getInstance()->getObjectTypeID('de.julian-pfeil.todolist.todo');

                    $sql = "SELECT	objectID
							FROM	wcf1_user_object_watch
							WHERE	objectTypeID = ? AND userID = ?";
                    $statement = WCF::getDB()->prepare($sql);
                    $statement->execute([$objectTypeID, WCF::getUser()->userID]);
                    self::$subscribedTodos = $statement->fetchAll(\PDO::FETCH_COLUMN);

                    // update storage data
                    UserStorageHandler::getInstance()->update(WCF::getUser()->userID, self::USER_STORAGE_SUBSCRIBED_TODOS, \serialize(self::$subscribedTodos));
                } else {
                    self::$subscribedTodos = \unserialize($data);
                }
            }
        }

        return self::$subscribedTodos;
    }

    /**
     * Returns true if the active user has subscribed to this todo.
     */
    public function isSubscribed()
    {
        return \in_array($this->todoID, self::getSubscribedTodoIDs());
    }

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        return LinkHandler::getInstance()->getControllerLink(TodoPage::class, [
            'id' => $this->todoID,
            'forceFrontend' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->todoName;
    }

    /**
     * @inheritDoc
     */
    public function getSubject()
    {
        return $this->getTitle();
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->description;
    }

    /**
     * Returns the formatted message.
     */
    public function getFormattedMessage(): string
    {
        $this->loadEmbeddedObjects();

        $processor = new HtmlOutputProcessor();
        $processor->process($this->description, 'de.julian-pfeil.todolist.todo.content', $this->todoID);

        return $processor->getHtml();
    }

    /**
     * Returns a simplified version of the formatted message.
     *
     * @return  string
     */
    public function getSimplifiedFormattedMessage()
    {
        // parse and return message
        $processor = new HtmlOutputProcessor();
        $processor->setOutputType('text/simplified-html');
        $processor->process($this->description, 'de.julian-pfeil.todolist.todo.content', $this->todoID);

        return $processor->getHtml();
    }

    /**
     * Returns a plain unformatted version of the message.
     *
     * @return  string
     */
    public function getPlainMessage()
    {
        // remove [readmore] tag
        $description = \str_replace('[readmore]', '', $this->getFormattedMessage());

        // parse and return message
        $processor = new HtmlOutputProcessor();
        $processor->setOutputType('text/plain');
        $processor->process($description, 'de.julian-pfeil.todolist.todo.content', $this->todoID);

        return $processor->getHtml();
    }

    /**
     * @inheritDoc
     */
    public function getExcerpt()
    {
        return StringUtil::truncateHTML($this->getSimplifiedFormattedMessage());
    }

    /**
     * @inheritDoc
     */
    public function getPlainExcerpt()
    {
        return StringUtil::truncate($this->getPlainMessage());
    }

    /**
     * Returns the category of this todo.
     */
    public function getCategory()
    {
        if ($this->category === null && $this->categoryID) {
            $this->category = TodoCategory::getCategory($this->categoryID);
        }

        return $this->category;
    }

    /**
     * Returns the user profile of the user who added the todo.
     */
    public function getUserProfile(): UserProfile
    {
        if ($this->userID) {
            return UserProfileRuntimeCache::getInstance()->getObject($this->userID);
        } else {
            return UserProfile::getGuestUserProfile($this->username);
        }
    }

    /**
     * Returns the user profile of the user who is set as current editor.
     */
    public function getCurrentEditorProfile(): UserProfile
    {
        if ($this->currentEditor) {
            return UserProfileRuntimeCache::getInstance()->getObject($this->currentEditor);
        }

        return null;
    }

    /**
     * Returns `true` if the active user can delete this todo and `false` otherwise.
     */
    public function canDelete(): bool
    {
        if (
            WCF::getUser()->userID == $this->userID
            && $this->category->canDeleteOwnTodo()
        ) {
            return true;
        }

        return $this->category->canDeleteTodo();
    }

    /**
     * Returns `true` if the active user can edit this todo and `false` otherwise.
     */
    public function canEdit(): bool
    {
        if (
            WCF::getUser()->userID == $this->userID
            && $this->category->canEditOwnTodo()
        ) {
            return true;
        }

        return $this->category->canEditTodo();
    }

    /**
     * Returns `true` if the active user can read this todo and `false` otherwise.
     */
    public function canRead(): bool
    {
        return $this->category->canView();
    }

    /**
     * @inheritdoc
     */
    public function isVisible()
    {
        return $this->canRead();
    }
}
