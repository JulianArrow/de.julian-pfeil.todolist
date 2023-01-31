<?php

namespace todolist\system\user\notification\event;

use todolist\system\cache\runtime\ViewableTodoRuntimeCache;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;
use wcf\system\user\object\watch\UserObjectWatchHandler;

/**
 * User notification event for subscribed todos.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.user.notification.event
 */
class TodoUserNotificationEvent extends AbstractSharedUserNotificationEvent
{
    /**
     * @inheritDoc
     */
    protected function prepare()
    {
        ViewableTodoRuntimeCache::getInstance()->cacheObjectID($this->getUserNotificationObject()->todoID);
    }

    /**
     * @inheritDoc
     */
    public function checkAccess()
    {
        if (!$this->getUserNotificationObject()->canRead()) {
            // remove subscription
            UserObjectWatchHandler::getInstance()->deleteObjects(
                'de.julian-pfeil.todolist.todo',
                [$this->getUserNotificationObject()->todoID],
                [WCF::getUser()->userID]
            );

            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        return [
            'message-id' => 'de.julian-pfeil.todolist.todo/' . $this->getUserNotificationObject()->todoID . '/' . TIME_NOW . '/' . \bin2hex(\random_bytes(8)),
            'template' => 'email_notification_edit',
            'application' => 'todolist',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        return $this->getUserNotificationObject()->getLink();
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->getLanguage()->getDynamicVariable('todolist.action.notification.message', [
            'todo' => $this->userNotificationObject,
            'author' => $this->author,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getLanguage()->get('todolist.action.notification.title');
    }
}
