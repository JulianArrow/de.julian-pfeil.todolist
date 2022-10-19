<?php

namespace todolist\page;

use todolist\data\todo\category\TodoCategory;
use todolist\data\todo\category\TodoCategoryNodeTree;
use todolist\data\todo\TodoEditor;
use todolist\data\todo\ViewableTodo;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\reaction\ReactionHandler;
use wcf\system\WCF;

/**
 * Shows the details of a certain todo.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage page
 */
class TodoPage extends AbstractPage
{
    /**
     * shown todo
     * @var ViewableTodo
     */
    public $todo;

    /**
     * id of the shown todo
     * @var int
     */
    public $todoID = 0;

    /**
     * @inheritDoc
     */
    public $neededPermissions = [];

    /**
     * category
     */
    public $category;

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'todo' => $this->todo,
            'category' => $this->category,
            'canAddTodoInAnyCategory' => $this->canAddTodoInAnyCategory,
        ]);

        if (MODULE_LIKE) {
            WCF::getTPL()->assign([
                'todoLikeData' => $this->todoLikeData,
            ]);
        }
    }

    /**
    * @inheritDoc
    */
    public function checkPermissions()
    {
        if (!$this->todo->canRead()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        //todoID
        if (isset($_REQUEST['id'])) {
            $this->todoID = \intval($_REQUEST['id']);
        }
        $this->todo = ViewableTodo::getTodo($this->todoID);
        if (!$this->todo->todoID) {
            throw new IllegalLinkException();
        }

        // set category
        $this->category = $this->todo->getCategory();
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        //set canAddTodoInAnyCategory
        $categoryNodeTree = new TodoCategoryNodeTree(TodoCategory::OBJECT_TYPE_NAME, 0, false);
        $categoryNodeTree->loadCategoryLists();

        $this->canAddTodoInAnyCategory = $categoryNodeTree->canAddTodoInAnyCategory();

        // update view count
        $todoEditor = new TodoEditor($this->todo->getDecoratedObject());
        $todoEditor->updateCounters([
            'views' => 1,
        ]);

        /* reactions */
        if (MODULE_LIKE) {
            $objectType = ReactionHandler::getInstance()->getObjectType('de.julian-pfeil.todolist.likeableTodo');
            ReactionHandler::getInstance()->loadLikeObjects($objectType, [$this->todoID]);
            $this->todoLikeData = ReactionHandler::getInstance()->getLikeObjects($objectType);
        }

        $this->todo->loadEmbeddedObjects();
        MessageEmbeddedObjectManager::getInstance()->setActiveMessage('de.julian-pfeil.todolist.todo', $this->todoID);
    }
}
