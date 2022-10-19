<?php

namespace todolist\system\user\notification\event;

use todolist\system\todo\TodoDataHandler;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;
use wcf\system\request\LinkHandler;

/**
 * User notification event for subscribed categories.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.user.notification.event
 */
class TodoCategoryUserNotificationEvent extends AbstractSharedUserNotificationEvent
{
    /**
     * @inheritDoc
     */
    public function checkAccess()
    {
        return $this->getUserNotificationObject()->canRead();
    }

    /**
     * @inheritDoc
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        return [
            'message-id' => 'de.julian-pfeil.todolist.todo/' . $this->getUserNotificationObject()->entryID,
            'template' => 'email_notification_category',
            'application' => 'todolist'
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        return LinkHandler::getInstance()->getLink('Todo', [
            'application' => 'todolist',
            'object' => $this->getUserNotificationObject()
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->getLanguage()->getDynamicVariable('todolist.category.notification.message', [
            'todo' => $this->userNotificationObject,
            'author' => $this->author
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getLanguage()->get('todolist.category.notification.title');
    }

    /**
     * @inheritDoc
     */
    protected function prepare()
    {
        TodoDataHandler::getInstance()->cacheTodoID($this->getUserNotificationObject()->entryID);
    }
}
