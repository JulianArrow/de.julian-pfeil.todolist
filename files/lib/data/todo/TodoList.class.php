<?php

namespace todolist\data\todo;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of todos.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage data.todo.list
 */
class TodoList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Todo::class;
}
