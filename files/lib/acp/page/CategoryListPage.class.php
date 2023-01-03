<?php

namespace todolist\acp\page;

use todolist\data\todo\category\TodoCategory;
use wcf\acp\page\AbstractCategoryListPage;

/**
 * Class CategoryListPage
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage acp.page
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
