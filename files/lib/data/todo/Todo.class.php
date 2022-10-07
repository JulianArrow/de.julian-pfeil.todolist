<?php

namespace todolist\data\todo;

use todolist\page\TodoPage;
use todolist\data\category\TodoCategory;

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
use wcf\system\category\CategoryHandler;

/**
 * Represents a todo.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package WoltLabSuite\Core\Data\Todo
 */
class Todo extends DatabaseObject implements ITitledLinkObject
{
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
        $this->category = $this->getCategory();
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
	 * Loads the embedded objects.
	 */
	public function loadEmbeddedObjects() {
		if ($this->hasEmbeddedObjects && !$this->embeddedObjectsLoaded) {
			MessageEmbeddedObjectManager::getInstance()->loadObjects('de.julian-pfeil.todolist.todo', [$this->todoID]);
			$this->embeddedObjectsLoaded = true;
		}
	}

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        return LinkHandler::getInstance()->getControllerLink(TodoPage::class, [
                'id' => $this->todoID
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
     * @return	string
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
     * @return	string
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
	 * Returns the todo object with the given id.
	 */
	public static function getTodo($todoID) {
		$todoList = new TodoList();
		$todoList->setObjectIDs([$todoID]);
		$todoList->readObjects();
		
		return $todoList->search($todoID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getExcerpt() {
        $excerpt = StringUtil::truncate($this->getPlainMessage());
		return $excerpt;
	}
    
    /**
     * Returns the category name
     *
     * @return mixed
     * @throws SystemException
     */
    public function getCategory()
    {
        return TodoCategory::getCategory($this->categoryID);
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
     * Returns `true` if the todo has labels and `false` otherwise.
     */
    public function hasLabels(): bool
    {
		if ($this->hasLabels == '1') {
			return true;
		}
		
		return false;
    }
    
    /**
     * Adds a label.
     */
    public function addLabel(Label $label)
    {
        $this->labels[$label->labelID] = $label;
    }

    /**
     * Returns a list of labels.
     */
    public function getLabels()
    {
        return $this->labels;
    }
	
    /**
     * Returns `true` if the todo is marked as done and `false` otherwise.
     */
    public function isDone(): bool
    {
		if ($this->isDone == '1') {
			return true;
		}
		
		return false;
    }
}
