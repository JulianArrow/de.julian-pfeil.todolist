<?php

namespace todolist\page;

use todolist\data\todo\category\TodoCategory;
use todolist\data\todo\category\TodoCategoryNodeTree;
use todolist\data\todo\TodoEditor;
use todolist\data\todo\ViewableTodo;
use wcf\page\AbstractPage;
use wcf\system\comment\CommentHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\page\PageLocationManager;
use wcf\system\reaction\ReactionHandler;
use wcf\system\WCF;

/**
 * Shows the details of a certain todo.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package     de.julian-pfeil.todolist
 * @subpackage  page
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
     * list of comments
     * @var StructuredCommentList
     */
    public $commentList;

    /**
     * todo comment manager object
     * @var TodoCommentManager
     */
    public $commentManager;

    /**
     * id of the todo comment object type
     * @var int
     */
    public $commentObjectTypeID = 0;

    public $canAddTodoInAnyCategory;

    public $todoLikeData;

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'commentCanAdd' => WCF::getSession()->getPermission('user.todolist.comment.canAddComment'),
            'commentList' => $this->commentList,
            'commentObjectTypeID' => $this->commentObjectTypeID,
            'lastCommentTime' => $this->commentList ? $this->commentList->getMinCommentTime() : 0,
            'likeData' => MODULE_LIKE && $this->commentList ? $this->commentList->getLikeData() : [],
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
        if ($this->todo === null) {
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

        /* comments */
        if ($this->todo->enableComments) {
            $this->commentObjectTypeID = CommentHandler::getInstance()->getObjectTypeID('de.julian-pfeil.todolist.todoComment');
            $this->commentManager = CommentHandler::getInstance()->getObjectType($this->commentObjectTypeID)->getProcessor();
            $this->commentList = CommentHandler::getInstance()->getCommentList($this->commentManager, $this->commentObjectTypeID, $this->todo->todoID);
        }

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

        /* breadcrumbs */
        PageLocationManager::getInstance()->addParentLocation('de.julian-pfeil.todolist.TodoList', $this->todo->categoryID, $this->todo->getDecoratedObject()->category);
        PageLocationManager::getInstance()->addParentLocation('de.julian-pfeil.todolist.TodoList');
    }
}
