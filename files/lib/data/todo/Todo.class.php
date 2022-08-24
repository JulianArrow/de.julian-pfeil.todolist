<?php

namespace todolist\data\todo;

use wcf\data\DatabaseObject;
use wcf\data\ITitledLinkObject;
use todolist\page\TodoPage;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\data\user\UserProfile;
use wcf\util\StringUtil;
use wcf\system\html\output\HtmlOutputProcessor;

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
     * Returns the name of the todo if a todo object is treated as a string.
     *
     * @return  string
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        return LinkHandler::getInstance()->getControllerLink(TodoPage::class, [
            'object' => $this,
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
	 * @inheritDoc
	 */
	public function getExcerpt() {
        $excerpt = StringUtil::truncate($this->getPlainMessage());
		return $excerpt;
	}

	/**
     * Returns `true` if the active user can delete this todo and `false` otherwise.
     */
    public function canDelete(): bool
    {
        if (
            WCF::getUser()->userID
            && WCF::getUser()->userID == $this->userID
            && WCF::getSession()->getPermission('user.todolist.canDeleteOwnTodos')
        ) {
            return true;
        }

        return WCF::getSession()->getPermission('mod.todolist.canDeleteTodos');
    }

    /**
     * Returns `true` if the active user can edit this todo and `false` otherwise.
     */
    public function canEdit(): bool
    {
        if (
            WCF::getUser()->userID
            && WCF::getUser()->userID == $this->userID
            && WCF::getSession()->getPermission('user.todolist.canEditOwnTodos')
        ) {
            return true;
        }

        return WCF::getSession()->getPermission('mod.todolist.canEditTodos');
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
     * Returns true if the todo is marked as done
     */
    public function isDone()
    {
		if ($this->done == '1') {
			return true;
		}
		
		return false;
    }
}
