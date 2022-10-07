<?php

namespace todolist\page;

use todolist\data\category\TodoCategoryNodeTree;
use todolist\data\category\TodoCategory;
use todolist\data\todo\TodoList;

use wcf\data\object\type\ObjectTypeCache;
use wcf\page\SortablePage;
use wcf\system\label\LabelHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\system\request\LinkHandler;

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
    public $objectListClassName = TodoList::class;

    /**
     * @inheritDoc
     */
    public $validSortFields = ['todoName', 'time'];

    /**
     * 0 if undone, 1 if done, empty if not set
     */
    public $requestDone = '';

    /**
     * category id
     */
    public $categoryID = '';

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
    public $category = null;
    
    /**
    * label filter
    */
   public $labelIDs = [];
   
   /**
    * list of available label groups
    */
   public $labelGroups = [];

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
            'labelGroups' => $this->labelGroups,
            'labelIDs' => $this->labelIDs,
            'canAddTodoInAnyCategory' => $this->categoryNodeTree->canAddTodoInAnyCategory()
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
            $this->requestDone = \intval($_REQUEST['isDone']);
        } else {
            $this->requestDone = '0';
        }
        
        $this->checkSortFields();

        $this->labelGroups = TodoCategory::getAccessibleLabelGroups('canViewLabel');
        if (!empty($this->labelGroups) && isset($_REQUEST['labelIDs']) && \is_array($_REQUEST['labelIDs'])) {
            $this->labelIDs = $_REQUEST['labelIDs'];

            foreach ($this->labelIDs as $groupID => $labelID) {
                $isValid = false;

                // ignore zero-values
                if (!\is_array($labelID) && $labelID) {
                    if (isset($this->labelGroups[$groupID]) && ($labelID == -1 || $this->labelGroups[$groupID]->isValid($labelID))) {
                        $isValid = true;
                    }
                }

                if (!$isValid) {
                    unset($this->labelIDs[$groupID]);
                }
            }
        }

        if (!empty($_POST)) {
            $labelParameters = '';
            if (!empty($this->labelIDs)) {
                foreach ($this->labelIDs as $groupID => $labelID) {
                    $labelParameters .= 'labelIDs[' . $groupID . ']=' . $labelID . '&';
                }
            }

            $controllerParameters = ['application' => 'todolist'];
            if ($this->categoryID) {
                $controllerParameters['categoryID'] = $this->categoryID;
            }

            HeaderUtil::redirect(
                LinkHandler::getInstance()->getLink(
                    'TodoList', 
                    $controllerParameters, 
                    \rtrim($labelParameters, 
                    '&')
                )
            );

            exit;
        }
    }

    /**
    * @inheritDoc
    */
    public function checkPermissions() {
        parent::checkPermissions();

        if ($this->categoryID) {
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
        if ($this->requestDone != '')
        {
            $this->objectList->getConditionBuilder()->add('isDone = ?', [$this->requestDone]);
        }

        if ($this->category !== null)
        {
            $this->objectList->getConditionBuilder()->add('categoryID = ?', [$this->category->categoryID]);
        }

        // filter by label
        if (!empty($this->labelIDs)) {
            $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.label.object', 'de.julian-pfeil.todolist.todo')->objectTypeID;

            foreach ($this->labelIDs as $groupID => $labelID) {
                if ($labelID == -1) {
                    $groupLabelIDs = LabelHandler::getInstance()->getLabelGroup($groupID)->getLabelIDs();

                    if (!empty($groupLabelIDs)) {
                        $this->objectList->getConditionBuilder()->add('links.linkID NOT IN (SELECT objectID FROM wcf' . WCF_N . '_label_object WHERE objectTypeID = ? AND labelID IN (?))', [
                            $objectTypeID,
                            $groupLabelIDs,
                        ]);
                    }
                } else {
                    $this->labelID = $labelID;
                    $this->objectList->getConditionBuilder()->add('links.linkID IN (SELECT objectID FROM wcf' . WCF_N . '_label_object WHERE objectTypeID = ? AND labelID = ?)', [
                        $objectTypeID,
                        $labelID,
                    ]);
                }
            }
        }
    }

    /**
     * check additional valid sort-fields
     */
    protected function checkSortFields()
    {
        if (MODULE_LIKE) 
        {
        $this->validSortFields[] = 'cumulativeLikes';
        }

        if (defined('TODOLIST_COMMENTS_PLUGIN')) 
        {
            $this->validSortFields[] = 'comments';
        }
    }
}
