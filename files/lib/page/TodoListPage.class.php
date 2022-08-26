<?php

namespace todolist\page;

use wcf\page\SortablePage;
use todolist\data\todo\TodoList;
use wcf\system\WCF;

/**
 * Shows the list of todos.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package WoltLabSuite\Core\Page
 */
class TodoListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $defaultSortField = 'creationDate';
    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'de.julian-pfeil.todolist.TodoList';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.todolist.general.canSeeTodos'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = TodoList::class;

    /**
     * @inheritDoc
     */
    public $validSortFields = ['todoID', 'todoName', 'creationDate', 'done'];

    /**
     * 0 if undone, 1 if done, empty if not set
     */
    public $doneParameter = '';

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'done' => $this->doneParameter,
            'validSortFields' => $this->validSortFields
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['done'])) {
            $this->doneParameter = \intval($_REQUEST['done']);
        }
        
        $this->checkSortFields();
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->applyFilters();
    }

    /**
     * applies filters
     */
    protected function applyFilters()
    {
        if ($this->doneParameter != '')
        {
            $this->objectList->getConditionBuilder()->add('done = ?', [$this->doneParameter]);
        }
    }

    /**
     * check additional valid sort-fields
     */
    protected function checkSortFields()
    {
        if (MODULE_TODOLIST_COMMENTS) 
        {
            $this->validSortFields[] = 'comments';
        }
        if (MODULE_TODOLIST_REACTIONS) 
        {
            $this->validSortFields[] = 'cumulativeLikes';
        }
    }
}
