<?php

namespace todolist\page;

use todolist\data\todo\AccessibleTodoList;
use todolist\data\todo\category\TodoCategory;
use todolist\data\todo\category\TodoCategoryNodeTree;
use wcf\page\SortablePage;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\page\PageLocationManager;
use wcf\system\WCF;

/**
 * Shows the list of todos.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage page
 */
class TodoListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $defaultSortField = 'time';

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
    public $neededPermissions = ['user.todolist.general.canViewTodoList'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = AccessibleTodoList::class;

    /**
     * @inheritDoc
     */
    public $validSortFields = ['todoName', 'time', 'comments', 'views', 'lastEditTime', 'cumulativeLikes'];

    /**
     * 0 if undone, 1 if done, empty if not set
     */
    public $requestDone = '';

    /**
     * category id
     */
    public $categoryID = 0;

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
     * category object
     *
     * @var        Category
     */
    public $category;

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'isDone' => $this->requestDone,
            'categoryID' => $this->categoryID,
            'validSortFields' => $this->validSortFields,
            'viewableCategoryList' => $this->viewableCategoryList,
            'canAddToCategoryList' => $this->canAddToCategoryList,
            'canAddTodoInAnyCategory' => $this->categoryNodeTree->canAddTodoInAnyCategory(),
        ]);

        if (!empty($this->category)) {
            WCF::getTPL()->assign([
                'category' => $this->category,
            ]);
        }
    }

    /**
     * @inheritDoc
     * @throws SystemException
     */
    public function readData()
    {
        parent::readData();

        $this->loadCategoryList();
        
        /* breadcrumbs */
        if ($this->categoryID > 0) {
            PageLocationManager::getInstance()->addParentLocation('de.julian-pfeil.todolist.TodoList', $this->categoryID, $this->category);
        }
    }

    /**
     * @throws SystemException
     * @throws Exception
     */
    private function loadCategoryList()
    {
        $categoryNodeTree = new TodoCategoryNodeTree(TodoCategory::OBJECT_TYPE_NAME, 0, false);
        $categoryNodeTree->loadCategoryLists();
        $this->categoryNodeTree = $categoryNodeTree;

        $this->viewableCategoryList = $categoryNodeTree->viewableCategoryList;
        $this->canAddToCategoryList = $categoryNodeTree->canAddToCategoryList;
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['categoryID']) && \intval($_REQUEST['categoryID']) > 0) {
            $this->categoryID = \intval($_REQUEST['categoryID']);
            $this->category = TodoCategory::getCategory($this->categoryID);

            if ($this->category === null) {
                throw new IllegalLinkException();
            }
        }

        if (isset($_REQUEST['isDone'])) {
            if ($_REQUEST['isDone'] == '') {
                $this->requestDone = '';
            } else {
                $this->requestDone = \intval($_REQUEST['isDone']);
            }
        } else {
            $this->requestDone = '0';
        }
    }

    /**
    * @inheritDoc
    */
    public function checkPermissions()
    {
        parent::checkPermissions();

        if ($this->categoryID > 0) {
            if (!$this->category->canView()) {
                throw new PermissionDeniedException();
            }
        }
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
        if ($this->requestDone != '') {
            $this->objectList->getConditionBuilder()->add('isDone = ?', [$this->requestDone]);
        }

        if ($this->category !== null) {
            $this->objectList->getConditionBuilder()->add('categoryID = ?', [$this->category->categoryID]);
        }
    }
}
