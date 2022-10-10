<?php

namespace todolist\acp\form;

use todolist\data\todo\category\TodoCategory;
use wcf\acp\form\AbstractCategoryAddForm;

/**
 * Class CategoryAddForm
 *
 * @author     Julian Pfeil <https://julian-pfeil.de>
 * @link    https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage acp.form
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
