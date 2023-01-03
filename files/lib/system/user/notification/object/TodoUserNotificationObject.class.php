<?php

namespace todolist\system\user\notification\object;

use todolist\data\todo\Todo;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\user\notification\object\IUserNotificationObject;

/**
 * Represents an todo as a notification object.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.user.notification.object
 */
class TodoUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Todo::class;

    /**
     * @inheritDoc
     */
    public function getAuthorID()
    {
        return $this->userID;
    }

    /**
     * @inheritDoc
     */
    public function getObjectID()
    {
        return $this->todoID;
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getDecoratedObject()->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function getURL()
    {
        return $this->getDecoratedObject()->getLink();
    }
}
