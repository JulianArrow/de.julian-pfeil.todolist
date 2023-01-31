<?php

namespace todolist\system\user\notification\event;

use todolist\system\todo\TodoDataHandler;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;
use wcf\system\user\notification\event\TReactionUserNotificationEvent;
use wcf\system\WCF;

/**
 * User notification event for todo comment response likes.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     License for Commercial Plugins <https://julian-pfeil.de/lizenz/>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.user.notification.event
 */
class TodoCommentResponseLikeUserNotificationEvent extends AbstractSharedUserNotificationEvent
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
    public function getEmailMessage($notificationType = 'instant')
    {
        /* not supported */
    }

    /**
     * @inheritDoc
     */
    public function getEventHash()
    {
        return \sha1($this->eventID . '-' . $this->getUserNotificationObject()->objectID);
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
        ], '#commentsTab/comment' . $this->additionalData['commentID'] . '/response' . $this->getUserNotificationObject()->objectID);
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        $todo = TodoDataHandler::getInstance()->getTodo($this->additionalData['objectID']);
        $authors = \array_values($this->getAuthors());
        $count = \count($authors);
        $commentUser = null;
        if ($this->additionalData['commentUserID'] != WCF::getUser()->userID) {
            $commentUser = UserProfileRuntimeCache::getInstance()->getObject($this->additionalData['commentUserID']);
        }

        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable('todolist.todo.comment.response.like.notification.message.stacked', [
                'author' => $this->author,
                'authors' => $authors,
                'commentID' => $this->additionalData['commentID'],
                'commentUser' => $commentUser,
                'count' => $count,
                'others' => $count - 1,
                'todo' => $todo,
                'responseID' => $this->getUserNotificationObject()->objectID,
                'reactions' => $this->getReactionsForAuthors(),
            ]);
        }

        return $this->getLanguage()->getDynamicVariable('todolist.comment.response.like.notification.message', [
            'author' => $this->author,
            'commentID' => $this->additionalData['commentID'],
            'todo' => $todo,
            'responseID' => $this->getUserNotificationObject()->objectID,
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
            return $this->getLanguage()->getDynamicVariable('todolist.todo.comment.response.like.notification.title.stacked', [
                'count' => $count,
                'timesTriggered' => $this->notification->timesTriggered,
            ]);
        }

        return $this->getLanguage()->get('todolist.todo.comment.response.like.notification.title');
    }

    /**
     * @inheritDoc
     */
    protected function prepare()
    {
        TodoDataHandler::getInstance()->cacheTodoID($this->additionalData['objectID']);
        UserProfileRuntimeCache::getInstance()->cacheObjectID($this->additionalData['commentUserID']);
    }

    /**
     * @inheritDoc
     */
    public function supportsEmailNotification()
    {
        return false;
    }
}
