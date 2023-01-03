<?php

namespace todolist\system\todo;

use todolist\data\todo\TodoList;
use wcf\system\SingletonFactory;

/**
 * Caches todo objects for todo-related user notifications.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.todo
 */
class TodoDataHandler extends SingletonFactory
{
    /**
     * list of cached todos
     */
    protected $todoIDs = [];

    protected $todos = [];

    /**
     * Caches an todo id.
     */
    public function cacheTodoID($todoID)
    {
        if (!\in_array($todoID, $this->todoIDs)) {
            $this->todoIDs[] = $todoID;
        }
    }

    /**
     * Returns the todo with the given id.
     */
    public function getTodo($todoID)
    {
        if (!empty($this->todoIDs)) {
            $this->todoIDs = \array_diff($this->todoIDs, \array_keys($this->todos));

            if (!empty($this->todoIDs)) {
                $todoList = new TodoList();
                $todoList->setObjectIDs($this->todoIDs);
                $todoList->readObjects();
                $this->todos += $todoList->getObjects();
                $this->todoIDs = [];
            }
        }

        if (isset($this->todos[$todoID])) {
            return $this->todos[$todoID];
        }

        return null;
    }
}
