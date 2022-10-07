<?php

namespace todolist\page;

use todolist\data\todo\Todo;
use todolist\data\todo\TodoEditor;
use todolist\data\category\TodoCategoryNodeTree;
use todolist\data\category\TodoCategory;
use todolist\system\label\object\TodoLabelObjectHandler;

use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\system\reaction\ReactionHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\data\tag\Tag;
use wcf\system\tagging\TagEngine;

/**
 * Shows the details of a certain todo.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package WoltLabSuite\Core\Page
 */
class TodoPage extends AbstractPage
{


    /**
     * shown todo
     * @var Todo
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

    /**
     * list of tags
     */
    public $tags = [];

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'todo' => $this->todo,
            'canAddTodoInAnyCategory' => $this->canAddTodoInAnyCategory,
        ]);

        if (MODULE_LIKE) {
            WCF::getTPL()->assign([
                'todoLikeData' => $this->todoLikeData
            ]);
        }

        if (defined('TODOLIST_COMMENTS_PLUGIN')) {
            WCF::getTPL()->assign([
                'commentCanAdd' => WCF::getSession()->getPermission('user.todolist.comments.canAddComments'),
                'commentList' => $this->commentList,
                'commentObjectTypeID' => $this->commentObjectTypeID,
                'lastCommentTime' => $this->commentList ? $this->commentList->getMinCommentTime() : 0,
                'likeData' => MODULE_LIKE && $this->commentList ? $this->commentList->getLikeData() : [],
            ]);
        }

        if (defined('TODOLIST_TAGGING_PLUGIN')) {
            WCF::getTPL()->assign([
                'tags' => $this->tags
            ]);
        }
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
        $todoEditor = new TodoEditor($this->todo);
        $todoEditor->updateCounters([
            'views' => 1,
        ]);
        
        /* reactions */
        if (MODULE_LIKE) {
            $objectType = ReactionHandler::getInstance()->getObjectType('de.julian-pfeil.todolist.likeableTodo');
			ReactionHandler::getInstance()->loadLikeObjects($objectType, [$this->todoID]);
			$this->todoLikeData = ReactionHandler::getInstance()->getLikeObjects($objectType);
        }

        /* comments */
        if (defined('TODOLIST_COMMENTS_PLUGIN')) {
            if ($this->todo->enableComments) {
                $this->commentObjectTypeID = CommentHandler::getInstance()->getObjectTypeID(
                    'de.julian-pfeil.todolist.todoComment'
                );
                $this->commentManager = CommentHandler::getInstance()->getObjectType(
                    $this->commentObjectTypeID
                )->getProcessor();
                $this->commentList = CommentHandler::getInstance()->getCommentList(
                    $this->commentManager,
                    $this->commentObjectTypeID,
                    $this->todo->todoID
                );
            }
        }

        /* tags */
        if (MODULE_TAGGING && defined('TODOLIST_TAGGING_PLUGIN') && WCF::getSession()->getPermission('user.tag.canViewTag')) {
            $this->tags = TagEngine::getInstance()->getObjectTags(
                'de.julian-pfeil.todolist.tagging',
                $this->todo->linkID,
                [($this->todo->languageID === null ? LanguageFactory::getInstance()->getDefaultLanguageID() : "")]
            );
        }

        /* labels */
        if (defined('TODOLIST_LABELS_PLUGIN')) {
            if ($this->todo->hasLabels) {
                $assignedLabels = TodoLabelObjectHandler::getInstance()->getAssignedLabels([$this->todoID]);
                if (isset($assignedLabels[$this->todoID])) {
                    foreach ($assignedLabels[$this->todoID] as $label) {
                        $this->todo->addLabel($label);
                    }
                }
            }
        }

		$this->todo->loadEmbeddedObjects();
		MessageEmbeddedObjectManager::getInstance()->setActiveMessage('de.julian-pfeil.todolist.todo', $this->todoID);
    }

    /**
    * @inheritDoc
    */
    public function checkPermissions() {
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

        #todoID
        if (isset($_REQUEST['id'])) {
            $this->todoID = \intval($_REQUEST['id']);
        }
        $this->todo = new Todo($this->todoID);
        if (!$this->todo->todoID) {
            throw new IllegalLinkException();
        }
    }
}
