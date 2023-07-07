<?php

namespace todolist\system\user\notification\event;

use todolist\system\todo\TodoDataHandler;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\email\Email;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;
use wcf\system\WCF;

/**
 * User notification event for todo owner for comment responses.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     License for Commercial Plugins <https://julian-pfeil.de/lizenz/>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.user.notification.event
 */
class TodoCommentResponseOwnerUserNotificationEvent extends AbstractSharedUserNotificationEvent
{
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
        $comment = CommentRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->commentID);
        $todo = TodoDataHandler::getInstance()->getTodo($comment->objectID);
        if ($comment->userID) {
            $commentAuthor = UserProfileRuntimeCache::getInstance()->getObject($comment->userID);
        } else {
            $commentAuthor = UserProfile::getGuestUserProfile($comment->username);
        }

        $messageID = '<de.julian-pfeil.todolist.comment/' . $comment->commentID . '@' . Email::getHost() . '>';

        return [
            'template' => 'email_notification_commentResponseOwner',
            'application' => 'wcf',
            'in-reply-to' => [$messageID],
            'references' => [$messageID],
            'variables' => [
                'commentAuthor' => $commentAuthor,
                'commentID' => $this->getUserNotificationObject()->commentID,
                'todo' => $todo,
                'responseID' => $this->getUserNotificationObject()->responseID,
                'languageVariablePrefix' => 'todolist.comment.responseOwner.notification',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getEventHash()
    {
        return \sha1($this->eventID . '-' . $this->getUserNotificationObject()->commentID);
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        $todo = TodoDataHandler::getInstance()->getTodo($this->additionalData['objectID']);

        return LinkHandler::getInstance()->getLink('Todo', [
            'application' => 'todolist',
            'object' => $todo,
        ], '#commentsTab/comment' . $this->getUserNotificationObject()->commentID);
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        $comment = CommentRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->commentID);
        if ($comment->userID) {
            $commentAuthor = UserProfileRuntimeCache::getInstance()->getObject($comment->userID);
        } else {
            $commentAuthor = UserProfile::getGuestUserProfile($comment->username);
        }
        $todo = TodoDataHandler::getInstance()->getTodo($comment->objectID);

        $authors = $this->getAuthors();
        if (\count($authors) > 1) {
            if (isset($authors[0])) {
                unset($authors[0]);
            }
            $count = \count($authors);

            return $this->getLanguage()->getDynamicVariable('todolist.comment.responseOwner.notification.message.stacked', [
                'author' => $commentAuthor,
                'authors' => \array_values($authors),
                'commentID' => $this->getUserNotificationObject()->commentID,
                'count' => $count,
                'todo' => $todo,
                'others' => $count - 1,
                'guestTimesTriggered' => $this->notification->guestTimesTriggered,
            ]);
        }

        return $this->getLanguage()->getDynamicVariable('todolist.comment.responseOwner.notification.message', [
            'todo' => $todo,
            'author' => $this->author,
            'commentAuthor' => $commentAuthor,
            'commentID' => $this->getUserNotificationObject()->commentID,
            'responseID' => $this->getUserNotificationObject()->responseID,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        $count = \count($this->getAuthors());
        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable('todolist.comment.responseOwner.notification.title.stacked', [
                'count' => $count,
                'timesTriggered' => $this->notification->timesTriggered,
            ]);
        }

        return $this->getLanguage()->get('todolist.comment.responseOwner.notification.title');
    }

    /**
     * @inheritDoc
     */
    protected function prepare()
    {
        TodoDataHandler::getInstance()->cacheTodoID($this->additionalData['objectID']);
        CommentRuntimeCache::getInstance()->cacheObjectID($this->getUserNotificationObject()->commentID);
        UserProfileRuntimeCache::getInstance()->cacheObjectID($this->additionalData['userID']);
    }
}
