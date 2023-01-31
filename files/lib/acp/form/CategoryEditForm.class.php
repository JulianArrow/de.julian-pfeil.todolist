<?php

namespace todolist\acp\form;

use todolist\data\todo\category\TodoCategory;
use wcf\acp\form\AbstractCategoryEditForm;

/**
 * Class CategoryEditForm
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage acp.form
 */
class CategoryEditForm extends AbstractCategoryEditForm
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
