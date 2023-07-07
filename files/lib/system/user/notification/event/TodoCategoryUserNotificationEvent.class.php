<?php

namespace todolist\system\user\notification\event;

use todolist\system\cache\runtime\ViewableTodoRuntimeCache;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;

/**
 * User notification event for subscribed categories.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package     de.julian-pfeil.todolist
 * @subpackage  system.user.notification.event
 */
class TodoCategoryUserNotificationEvent extends AbstractSharedUserNotificationEvent
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
        return $this->getUserNotificationObject()->canRead();
    }

    /**
     * @inheritDoc
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        return [
            'message-id' => 'de.julian-pfeil.todolist.todo/' . $this->getUserNotificationObject()->todoID,
            'template' => 'email_notification_category',
            'application' => 'todolist',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return $this->getUserNotificationObject()->getLink();
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->getLanguage()->getDynamicVariable('todolist.category.notification.message', [
            'todo' => $this->userNotificationObject,
            'author' => $this->author,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->getLanguage()->get('todolist.category.notification.title');
    }
}
