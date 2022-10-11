<?php

namespace todolist\data\todo;

use todolist\page\TodoPage;
use todolist\data\todo\category\TodoCategory;
use wcf\data\DatabaseObject;
use wcf\data\ITitledLinkObject;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\data\user\UserProfile;
use wcf\data\label\Label;
use wcf\util\StringUtil;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Represents a todo.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
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
     * list of labels
     */
    protected $labels = [];

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
        if ($this->todo === null) {
            $this->todo = new self($this->todoID);
        }

        return $this->todo;
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
     * @inheritDoc
     */
    public function getLink()
    {
        return LinkHandler::getInstance()->getControllerLink(TodoPage::class, [
                'id' => $this->todoID,
                'forceFrontend' => true
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getLogLink()
    {
        return LinkHandler::getInstance()->getControllerLink(TodoLogPage::class, [
                'id' => $this->todoID,
                'forceFrontend' => true
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
        // remove [readmore] tag
        $description = \str_replace('[readmore]', '', $this->getFormattedMessage());

        // parse and return message
        $processor = new HtmlOutputProcessor();
        $processor->setOutputType('text/simplified-html');
        $processor->process($description, 'de.julian-pfeil.todolist.todo.content', $this->todoID);

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
        $excerpt = StringUtil::truncate($this->getPlainMessage());
        return $excerpt;
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
     * Returns the user profile of the user who last commented on the todo.
     */
    public function getLastCommentUserProfile()
    {
        if ($this->userID) {
            return UserProfileRuntimeCache::getInstance()->getObject($this->lastCommentUserID);
        } else {
            return UserProfile::getGuestUserProfile($this->lastCommentUserID);
        }
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
