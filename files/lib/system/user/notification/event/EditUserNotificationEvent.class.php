<?php

namespace todolist\system\user\notification\event;

use wcf\system\user\notification\event\AbstractUserNotificationEvent;
use wcf\system\WCF;

/**
 * Notification event for todo-edits.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.user.notification.event
 */
class EditUserNotificationEvent extends AbstractUserNotificationEvent
{
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
        return [
            'message-id' => 'de.julian-pfeil.todolist.todo/' . $this->getUserNotificationObject()->todoID,
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
