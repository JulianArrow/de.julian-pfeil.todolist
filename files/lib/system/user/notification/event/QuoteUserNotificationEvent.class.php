<?php

namespace todolist\system\user\notification\event;

use todolist\system\cache\runtime\ViewableTodoRuntimeCache;
use wcf\system\email\Email;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\WCF;

/**
 * Notification event for quotes in todos.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.user.notification.event
 */
class QuoteUserNotificationEvent extends AbstractSharedUserNotificationEvent
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
        return $this->getLanguage()->getDynamicVariable('todolist.todo.quote.notification.title');
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->getLanguage()->getDynamicVariable('todolist.todo.quote.notification.message', [
            'userNotificationObject' => $this->getUserNotificationObject(),
            'author' => $this->author,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        $messageID = '<de.julian-pfeil.todolist.todo/' . $this->getUserNotificationObject()->todoID . '@' . Email::getHost() . '>';

        return [
            'message-id' => 'de.julian-pfeil.todolist.todo/' . $this->getUserNotificationObject()->todoID,
            'template' => 'email_notification_quote',
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
        return $this->getLanguage()->getDynamicVariable('todolist.todo.quote.notification.mail.title', [
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
