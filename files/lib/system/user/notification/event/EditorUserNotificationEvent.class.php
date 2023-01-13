<?php

namespace todolist\system\user\notification\event;

use todolist\system\cache\runtime\ViewableTodoRuntimeCache;
use wcf\system\email\Email;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\WCF;

/**
 * Notification event for editors.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.user.notification.event
 */
class EditorUserNotificationEvent extends AbstractSharedUserNotificationEvent
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
    public function getTitle()
    {
        return $this->getLanguage()->getDynamicVariable('todolist.todo.editor.notification.title');
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->getLanguage()->getDynamicVariable('todolist.todo.editor.notification.message', [
            'userNotificationObject' => $this->getUserNotificationObject(),
            'author' => $this->author,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        return [
            'message-id' => 'de.julian-pfeil.todolist.todo/' . $this->getUserNotificationObject()->todoID . '/' . TIME_NOW . '/' . \bin2hex(\random_bytes(8)),
            'template' => 'email_notification_editor',
            'application' => 'todolist',
            'variables' => [
                'todo' => $this->getUserNotificationObject(),
                'author' => $this->author,
            ],
        ];
    }

    /**
     * @inheritDoc
     * @since   5.0
     */
    public function getEmailTitle()
    {
        return $this->getLanguage()->getDynamicVariable('todolist.todo.editor.notification.mail.title', [
            'userNotificationObject' => $this->getUserNotificationObject(),
        ]);
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
}
