<?php
namespace todolist\data\modification\log;

use todolist\data\todo\Todo;

use wcf\data\modification\log\IViewableModificationLog;
use wcf\data\modification\log\ModificationLog;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\WCF;

/**
 * Provides a viewable todo modification log.
 * 
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class TodoModificationLog extends DatabaseObjectDecorator implements IViewableModificationLog {
    /**
     * @inheritDoc
     */
    protected static $baseClass = ModificationLog::class;
    
    /**
     * Todo this modification log belongs to
     */
    protected $todo;
    
    /**
     * user profile object
     */
    protected $userProfile;
    
    /**
     * Returns readable representation of current log todo.
     */
    public function __toString() {
        return WCF::getLanguage()->getDynamicVariable('todolist.todo.log.todo.'.$this->action, ['additionalData' => $this->additionalData]);
    }
    
    /**
     * Returns the user profile object.
     */
    public function getUserProfile() {
        if ($this->userProfile === null) {
            $this->userProfile = new UserProfile(new User(null, $this->getDecoratedObject()->data));
        }
        
        return $this->userProfile;
    }
    
    /**
     * Sets the todo this modification log belongs to.
     */
    public function setTodo(Todo $todo) {
        if ($todo->todoID == $this->objectID) {
            $this->todo = $todo;
        }
    }
    
    /**
     * @inheritDoc
     */
    public function getAffectedObject() {
        return $this->todo;
    }
}
