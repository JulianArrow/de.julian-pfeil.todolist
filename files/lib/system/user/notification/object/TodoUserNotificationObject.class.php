<?php
namespace todolist\system\user\notification\object;
use todolist\data\todo\Todo;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\user\notification\object\IUserNotificationObject;
use wcf\system\WCF;

/**
 * Represents an todo as a notification object.
 * 
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class TodoUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject {
    /**
     * @inheritDoc
     */
    protected static $baseClass = Todo::class;
    
    /**
     * @inheritDoc
     */
    public function getAuthorID() {
        return WCF::getUser()->userID;
    }
    
    /**
     * @inheritDoc
     */
    public function getObjectID() {
        return $this->todoID;
    }

    /**
     * @inheritDoc
     */
    public function getTitle() {
        return $this->getDecoratedObject()->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function getURL() {
        return $this->getDecoratedObject()->getLink();
    }
}
