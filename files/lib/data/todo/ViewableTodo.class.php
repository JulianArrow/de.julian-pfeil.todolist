<?php

namespace todolist\data\todo;

use todolist\data\todo\list\ViewableTodoList;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\user\User;
use wcf\data\user\UserProfile;

/**
 * Represents a viewable todo.
 *
 * @author     Julian Pfeil <https://julian-pfeil.de>
 * @todo    https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage data.todo
 */
class ViewableTodo extends DatabaseObjectDecorator
{
    /**
     * @inheritdoc
     */
    protected static $baseClass = Todo::class;

    /**
     * user profile object
     */
    protected $userProfile;

    /**
     * content
     */
    protected $content;

    /**
     * todos category
     * @var Category
     */
    public $category;

    /**
     * @inheritDoc
     */
    public function __construct(DatabaseObject $object)
    {
        parent::__construct($object);

        $this->getDecoratedObject()->getCategory();
    }

    /**
     * Returns the name of the todo if a todo object is treated as a string.
     *
     * @return  string
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * Returns the user profile object
     */
    public function getUserProfile()
    {
        if ($this->userProfile === null) {
            $this->userProfile = new UserProfile(new User(null, $this->getDecoratedObject()->data));
        }

        return $this->userProfile;
    }

    /**
     * Gets a specific todo decorated as viewable todo.
     */
    public static function getTodo($todoID)
    {
        $todoList = new ViewableTodoList();
        $todoList->setObjectIDs([$todoID]);
        $todoList->readObjects();
        $objects = $todoList->getObjects();

        if (isset($objects[$todoID])) {
            return $objects[$todoID];
        }

        return null;
    }

    /**
     * Returns `true` if the todo is marked as done and `false` otherwise.
     */
    public function isDone(): bool
    {
        if ($this->isDone == '1') {
            return true;
        }

        return false;
    }
}
