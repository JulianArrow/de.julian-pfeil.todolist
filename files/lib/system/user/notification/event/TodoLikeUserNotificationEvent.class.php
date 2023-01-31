<?php

namespace todolist\system\user\notification\event;

use todolist\system\todo\TodoDataHandler;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;
use wcf\system\user\notification\event\TReactionUserNotificationEvent;
use wcf\system\WCF;

/**
 * User notification event for todo likes.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.user.notification.event
 */
class TodoLikeUserNotificationEvent extends AbstractSharedUserNotificationEvent
{
    use TReactionUserNotificationEvent;

    /**
     * @inheritDoc
     */
    protected $stackable = true;

    /**
     * @inheritDoc
     */
    public function checkAccess()
    {
        return WCF::getSession()->getPermission('user.todolist.general.canViewTodoList');
    }

    /**
     * @inheritDoc
     */
    public function getEventHash()
    {
        return \sha1($this->eventID . '-' . $this->additionalData['objectID']);
    }

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        $todo = TodoDataHandler::getInstance()->getTodo($this->additionalData['objectID']);

        return LinkHandler::getInstance()->getLink('Todo', [
            'application' => 'todolist',
            'object' => $todo,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        $todo = TodoDataHandler::getInstance()->getTodo($this->additionalData['objectID']);
        $authors = \array_values($this->getAuthors());
        $count = \count($authors);

        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable('todolist.like.notification.message.stacked', [
                'author' => $this->author,
                'authors' => $authors,
                'count' => $count,
                'others' => $count - 1,
                'todo' => $todo,
                'reactions' => $this->getReactionsForAuthors(),
            ]);
        }

        return $this->getLanguage()->getDynamicVariable('todolist.like.notification.message', [
            'author' => $this->author,
            'todo' => $todo,
            'reactions' => $this->getReactionsForAuthors(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        $count = \count($this->getAuthors());
        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable('todolist.like.notification.title.stacked', [
                'count' => $count,
                'timesTriggered' => $this->notification->timesTriggered,
            ]);
        }

        return $this->getLanguage()->getDynamicVariable('todolist.like.notification.title');
    }

    /**
     * @inheritDoc
     */
    protected function prepare()
    {
        TodoDataHandler::getInstance()->cachetodoID($this->additionalData['objectID']);
    }

    /**
     * @inheritDoc
     */
    public function supportsEmailNotification()
    {
        return false;
    }
}
