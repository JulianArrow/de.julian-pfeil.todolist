<?php

namespace todolist\acp\page;

use todolist\data\category\TodoCategory;
use wcf\acp\page\AbstractCategoryListPage;

/**
 * Class CategoryListPage
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class CategoryListPage extends AbstractCategoryListPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'todolist.acp.menu.link.todo.category';

    /**
     * @inheritDoc
     */
    public $objectTypeName = TodoCategory::OBJECT_TYPE_NAME;
}
