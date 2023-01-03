<?php

namespace todolist\data\todo\category;

use todolist\data\todo\Todo;
use wcf\system\category\CategoryHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the category cache for the to-do list.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage data.todo.category
 */
class TodoCategoryCache extends SingletonFactory
{
    /**
     * number of total todos
     */
    protected $todos;

    /**
     * last accessible todo of a category
     */
    protected $lastTodo;

    /**
     * number of unread todos
     */
    protected $unreadTodos;

    /**
     * Calculates the number of todos
     */
    protected function initTodos()
    {
        $this->todos = [];

        $sql = "SELECT   COUNT(*) AS count, categoryID
                FROM     todolist" . WCF_N . "_todo todos
                GROUP BY todos.categoryID";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $todos = $statement->fetchMap('categoryID', 'count');

        $categoryToParent = [];

        foreach (CategoryHandler::getInstance()->getCategories(TodoCategory::OBJECT_TYPE_NAME) as $category) {
            if (!isset($categoryToParent[$category->parentCategoryID])) {
                $categoryToParent[$category->parentCategoryID] = [];
            }
            $categoryToParent[$category->parentCategoryID][] = $category->categoryID;
        }

        $result = [];
        $this->countTodos($categoryToParent, $todos, 0, $result);
        $this->todos = $result;
    }

    /**
     * Counts the todos contained in this category.
     */
    protected function countTodos(array &$categoryToParent, array &$todos, $categoryID, array &$result)
    {
        $count = (isset($todos[$categoryID])) ? $todos[$categoryID] : 0;
        if (isset($categoryToParent[$categoryID])) {
            foreach ($categoryToParent[$categoryID] as $childCategoryID) {
                $count += $this->countTodos($categoryToParent, $todos, $childCategoryID, $result);
            }
        }

        if ($categoryID) {
            $result[$categoryID] = $count;
        }

        return $count;
    }

    /**
     * Returns the number of todos in the category.
     */
    public function getTodos($categoryID)
    {
        if ($this->todos === null) {
            $this->initTodos();
        }

        if (isset($this->todos[$categoryID])) {
            return $this->todos[$categoryID];
        }

        return 0;
    }

    /**
     * Calculates a list of the last accessible link entries of the category
     */
    protected function initLastTodos()
    {
        $this->lastTodo = [];

        $sql = "SELECT   *
                FROM     todolist" . WCF_N . "_todo todos
                ORDER BY todos.time DESC";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
        while ($row = $statement->fetchArray()) {
            if (!isset($this->lastEntry[$row['categoryID']])) {
                $this->lastEntry[$row['categoryID']] = new Todo(null, $row);
            }
        }
    }

    /**
     * Returns last todo of the category
     *
     * @param     $categoryID
     */
    public function getLastTodo($categoryID)
    {
        if ($this->lastTodo === null) {
            $this->initLastTodos();
        }

        if (isset($this->lastTodo[$categoryID])) {
            return $this->lastTodo[$categoryID];
        }

        return null;
    }
}
