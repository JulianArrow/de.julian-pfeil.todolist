<?php

namespace todolist\data\todo\list;

use todolist\data\todo\category\TodoCategory;

/**
 * Represents an accessible list of todos.
 *
 * @author     Julian Pfeil <https://julian-pfeil.de>
 * @link    https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage data.todo.list
 */
class AccessibleTodoList extends ViewableTodoList
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        // categories
        $accessibleCategoryIDs = TodoCategory::getAccessibleCategoryIDs();
        if (!empty($accessibleCategoryIDs)) {
            $this->getConditionBuilder()->add('todo.categoryID IN (?)', [$accessibleCategoryIDs]);
        } else {
            $this->getConditionBuilder()->add('1=0');
        }
    }

    public function readObjects()
    {
        if ($this->objectIDs === null) {
            $this->readObjectIDs();
        }

        parent::readObjects();
    }
}
