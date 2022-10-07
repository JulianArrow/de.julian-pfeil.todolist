<?php

namespace todolist\acp\form;

use todolist\data\category\TodoCategory;

use wcf\acp\form\AbstractCategoryAddForm;

/**
 * Class CategoryAddForm
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class CategoryAddForm extends AbstractCategoryAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'todolist.acp.menu.link.todo.category.add';

    /**
     * @inheritDoc
     */
    public $objectTypeName = TodoCategory::OBJECT_TYPE_NAME;

    /**
     * @inheritDoc
     */
    public $title = 'todolist.acp.menu.link.todo.category.add';
}