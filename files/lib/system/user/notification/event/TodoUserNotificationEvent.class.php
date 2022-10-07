<?php
namespace todolist\system\user\notification\event;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractUserNotificationEvent;
use wcf\system\WCF;

/**
 * Notification event for new todos.
 * 
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class TodoUserNotificationEvent extends AbstractUserNotificationEvent {
    /**
     * @inheritDoc
     */
    public function checkAccess() {
        return WCF::getSession()->getPermission('user.todolist.general.canSeeTodos');
    }
    
    /**
     * @inheritDoc
     */
    public function getEmailMessage($notificationType = 'instant') {
        return [
                'message-id' => 'de.julian-pfeil.todolist.todo/'.$this->getUserNotificationObject()->todoID,
                'template' => 'email_notification_todo',
                'application' => 'todolist'
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function getLink() {
        return LinkHandler::getInstance()->getLink('Todo', [
                'application' => 'todolist',
                'object' => $this->getUserNotificationObject()
        ]);
    }
    
    /**
     * @inheritDoc
     */
    public function getMessage() {
        return $this->getLanguage()->getDynamicVariable('todolist.action.notification.message', [
                'todo' => $this->userNotificationObject,
                'author' => $this->author,
        ]);
    }
    
    /**
     * @inheritDoc
     */
    public function getTitle() {
        return $this->getLanguage()->get('todolist.action.notification.title');
    }
}
