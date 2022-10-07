<?php

namespace todolist\data\category;

use wcf\data\category\CategoryNode;
use wcf\data\category\CategoryNodeTree;

/**
 * Class TodoCategoryNodeTree
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
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
    public $accessibleCategoryList = [];

    /**
     * @inheritDoc
     */
    public function isIncluded(CategoryNode $categoryNode)
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

        $this->categoryList = iterator_to_array($this->categoryList);

        $this->accessibleCategoryList = $this->categoryList;

        foreach ($this->accessibleCategoryList as $categoryItem) {
            if (!$categoryItem->canAddTodo()) {
                $key = \array_search($categoryItem, $this->accessibleCategoryList);
                unset($this->accessibleCategoryList[$key]);
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