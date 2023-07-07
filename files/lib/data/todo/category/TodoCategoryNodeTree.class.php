<?php

namespace todolist\data\todo\category;

use wcf\data\category\CategoryNode;
use wcf\data\category\CategoryNodeTree;

/**
 * Class TodoCategoryNodeTree
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package     de.julian-pfeil.todolist
 * @subpackage  data.todo.category
 */
class TodoCategoryNodeTree extends CategoryNodeTree
{
    /**
     * @inheritDoc
     */
    protected $nodeClassName = TodoCategoryNode::class;

    /**
     * @var TodoCategory[]
     */
    public $categoryList = [];

    /**
     * @var TodoCategory[]
     */
    public $viewableCategoryList = [];

    /**
     * @var TodoCategory[]
     */
    public $canAddToCategoryList = [];

    /**
     * @inheritDoc
     */
    public function isIncluded(CategoryNode $categoryNode): bool
    {
        /** @var TodoCategoryNode $categoryNode */
        return parent::isIncluded($categoryNode) && $categoryNode->isAccessible();
    }

    /**
     * @throws SystemException
     * @throws Exception
     */
    public function loadCategoryLists()
    {
        $categoryList = $this;
        $this->categoryList = $categoryList->getIterator();
        $this->categoryList->setMaxDepth(0);

        $this->categoryList = \iterator_to_array($this->categoryList);

        $this->viewableCategoryList = $this->categoryList;
        foreach ($this->viewableCategoryList as $categoryItem) {
            if (!$categoryItem->canView()) {
                $key = \array_search($categoryItem, $this->viewableCategoryList);
                unset($this->viewableCategoryList[$key]);
            }
        }

        $this->canAddToCategoryList = $this->categoryList;
        foreach ($this->canAddToCategoryList as $categoryItem) {
            if (!$categoryItem->canAddTodo()) {
                $key = \array_search($categoryItem, $this->canAddToCategoryList);
                unset($this->canAddToCategoryList[$key]);
            }
        }
    }

    /**
     * checks if user can add todo in any category
     */
    public function canAddTodoInAnyCategory()
    {
        foreach ($this->categoryList as $categoryItem) {
            if ($categoryItem->canAddTodo()) {
                return true;
            }
        }

        return false;
    }

    /**
     * checks if user can add todo in any category
     */
    public function canViewAnyCategory()
    {
        foreach ($this->categoryList as $categoryItem) {
            if ($categoryItem->canView()) {
                return true;
            }
        }

        return false;
    }
}
