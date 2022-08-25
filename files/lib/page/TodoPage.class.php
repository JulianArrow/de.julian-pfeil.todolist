<?php

namespace todolist\page;

use wcf\page\AbstractPage;
use todolist\data\todo\Todo;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\system\reaction\ReactionHandler;

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
    public $neededPermissions = ['user.todolist.general.canSeeTodos'];

    /**
     * list of comments
     * @var StructuredCommentList
     */
    public $commentList;

    /**
     * person comment manager object
     * @var PersonCommentManager
     */
    public $commentManager;

    /**
     * id of the person comment object type
     * @var int
     */
    public $commentObjectTypeID = 0;

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'todo' => $this->todo,
        ]);

        if (MODULE_TODOLIST_REACTIONS) {
            WCF::getTPL()->assign([
                'todoLikeData' => $this->todoLikeData
            ]);
        }

        if (MODULE_TODOLIST_COMMENTS) {
            WCF::getTPL()->assign([
                'commentCanAdd' => WCF::getSession()->getPermission('user.todolist.comments.canAddComments'),
                'commentList' => $this->commentList,
                'commentObjectTypeID' => $this->commentObjectTypeID,
                'lastCommentTime' => $this->commentList ? $this->commentList->getMinCommentTime() : 0,
                'likeData' => MODULE_LIKE && $this->commentList ? $this->commentList->getLikeData() : [],
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();
        
        if (MODULE_TODOLIST_REACTIONS) {
            $objectType = ReactionHandler::getInstance()->getObjectType('de.julian-pfeil.todolist.likeableTodo');
			ReactionHandler::getInstance()->loadLikeObjects($objectType, [$this->todoID]);
			$this->todoLikeData = ReactionHandler::getInstance()->getLikeObjects($objectType);
        }

        if (MODULE_TODOLIST_COMMENTS) {
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
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->todoID = \intval($_REQUEST['id']);
        }
        $this->todo = new Todo($this->todoID);
        if (!$this->todo->todoID) {
            throw new IllegalLinkException();
        }
    }
}
