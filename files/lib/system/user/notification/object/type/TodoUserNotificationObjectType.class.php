<?php
namespace todolist\system\user\notification\object\type;
use todolist\data\todo\Todo;
use todolist\data\todo\TodoList;
use todolist\system\user\notification\object\TodoUserNotificationObject;
use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;

/**
 * Represents an todo as a notification object type.
 * 
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class TodoUserNotificationObjectType extends AbstractUserNotificationObjectType {
    /**
     * @inheritDoc
     */
    protected static $decoratorClassName = TodoUserNotificationObject::class;
    
    /**
     * @inheritDoc
     */
    protected static $objectClassName = Todo::class;
    
    /**
     * @inheritDoc
     */
    protected static $objectListClassName = TodoList::class;
}
