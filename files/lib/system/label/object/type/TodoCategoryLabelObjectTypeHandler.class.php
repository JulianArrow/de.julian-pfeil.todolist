<?php

namespace todolist\system\label\object\type;

use todolist\data\category\TodoCategoryNodeTree;
use todolist\system\cache\builder\TodoCategoryLabelCacheBuilder;

use wcf\system\label\object\type\LabelObjectTypeContainer;
use wcf\system\label\object\type\AbstractLabelObjectTypeHandler;
use wcf\system\label\object\type\LabelObjectType;

/**
 * Object type handler implementation for todo categories.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class TodoCategoryLabelObjectTypeHandler extends AbstractLabelObjectTypeHandler
{
    /**
     * category list
     */
    public $categoryList;

    /**
     * @inheritdoc
     */
    protected function init()
    {
        $categoryTree = new TodoCategoryNodeTree('de.julian-pfeil.todolist.todo.category');
        $this->categoryList = $categoryTree->getIterator();
        $this->categoryList->setMaxDepth(0);
    }

    /**
     * @inheritdoc
     */
    public function setObjectTypeID($objectTypeID)
    {
        parent::setObjectTypeID($objectTypeID);

        $this->container = new LabelObjectTypeContainer($this->objectTypeID);
        foreach ($this->categoryList as $category) {
            $this->iterateCategory($category, 0);
        }
    }

    /**
     * iterates categories and adds them to container
     */
    protected function iterateCategory($category, $depth)
    {
        $this->container->add(new LabelObjectType($category->getTitle(), $category->categoryID, $depth));
        foreach ($category as $subCategory) {
            $this->iterateCategory($subCategory, $depth + 1);
        }
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        TodoCategoryLabelCacheBuilder::getInstance()->reset();
    }
}
