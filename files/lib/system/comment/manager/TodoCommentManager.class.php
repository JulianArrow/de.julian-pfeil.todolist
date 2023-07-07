<?php

namespace todolist\system\comment\manager;

use todolist\data\todo\Todo;
use todolist\data\todo\TodoEditor;
use todolist\data\todo\ViewableTodoList;
use todolist\system\cache\runtime\ViewableTodoRuntimeCache;
use wcf\data\comment\CommentList;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\comment\manager\AbstractCommentManager;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\WCF;

/**
 * Comment manager implementation for todo.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package     de.julian-pfeil.todolist
 * @subpackage  system.comment.manager
 */
class TodoCommentManager extends AbstractCommentManager implements IViewableLikeProvider
{
    /**
     * @inheritDoc
     */
    protected $permissionAdd = 'user.todolist.comment.canAddComment';

    /**
         * @inheritDoc
         */
    protected $permissionAddWithoutModeration = 'user.todolist.comment.canAddCommentWithoutModeration';

    /**
         * @inheritDoc
         */
    protected $permissionCanModerate = 'mod.todolist.comment.canModerateComment';

    /**
         * @inheritDoc
         */
    protected $permissionDelete = 'user.todolist.comment.canDeleteOwnComment';

    /**
         * @inheritDoc
         */
    protected $permissionEdit = 'user.todolist.comment.canEditOwnComment';

    /**
         * @inheritDoc
         */
    protected $permissionModDelete = 'mod.todolist.comment.canDeleteComment';

    /**
         * @inheritDoc
         */
    protected $permissionModEdit = 'mod.todolist.comment.canEditComment';

    /**
         * @inheritDoc
         */
    public function getLink($objectTypeID, $objectID)
    {
        return ViewableTodoRuntimeCache::getInstance()->getObject($objectID)->getLink();
    }

    /**
     * @inheritDoc
     */
    public function isAccessible($objectID, $validateWritePermission = false)
    {
        return ViewableTodoRuntimeCache::getInstance()->getObject($objectID) !== null;
    }

    /**
     * @inheritDoc
     */
    public function getTitle($objectTypeID, $objectID, $isResponse = false)
    {
        if ($isResponse) {
            return WCF::getLanguage()->get('todolist.comment.response');
        }

        return WCF::getLanguage()->getDynamicVariable('todolist.comment.title');
    }

    /**
     * @inheritDoc
     */
    public function updateCounter($objectID, $value)
    {
        (new TodoEditor(new Todo($objectID)))->updateCounters(['comments' => $value]);
    }

    /**
     * @inheritdoc
     */
    public function prepare(array $likes)
    {
        if (!WCF::getSession()->getPermission('user.todolist.general.canViewTodoList')) {
            return;
        }

        $commentLikeObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.like.likeableObject', 'com.woltlab.wcf.comment');
        $commentIDs = $responseIDs = [];
        foreach ($likes as $like) {
            if ($like->objectTypeID == $commentLikeObjectType->objectTypeID) {
                $commentIDs[] = $like->objectID;
            } else {
                $responseIDs[] = $like->objectID;
            }
        }

        // fetch response
        $userIDs = $responses = [];
        if (!empty($responseIDs)) {
            $responseList = new CommentResponseList();
            $responseList->setObjectIDs($responseIDs);
            $responseList->readObjects();
            $responses = $responseList->getObjects();
            foreach ($responses as $response) {
                $commentIDs[] = $response->commentID;
                if ($response->userID) {
                    $userIDs[] = $response->userID;
                }
            }
        }

        // fetch comments
        $commentList = new CommentList();
        $commentList->setObjectIDs($commentIDs);
        $commentList->readObjects();
        $comments = $commentList->getObjects();

        // fetch users
        $users = [];
        $todoIDs = [];
        foreach ($comments as $comment) {
            $todoIDs[] = $comment->objectID;
            if ($comment->userID) {
                $userIDs[] = $comment->userID;
            }
        }
        if (!empty($userIDs)) {
            $users = UserProfileRuntimeCache::getInstance()->getObjects(\array_unique($userIDs));
        }

        $todos = [];
        if (!empty($todoIDs)) {
            $todoList = new ViewableTodoList();
            $todoList->setObjectIDs($todoIDs);
            $todoList->readObjects();
            $todos = $todoList->getObjects();
        }

        // set message
        foreach ($likes as $like) {
            if ($like->objectTypeID == $commentLikeObjectType->objectTypeID) {
                // comment like
                if (isset($comments[$like->objectID])) {
                    $comment = $comments[$like->objectID];
                    if (isset($todos[$comment->objectID]) && $todos[$comment->objectID]->canRead()) {
                        $like->setIsAccessible();
                        // short output
                        $text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.de.julian-pfeil.todolist.todoComment', [
                            'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
                            'comment' => $comment,
                            'todo' => $todos[$comment->objectID],
                            'like' => $like,
                        ]);
                        $like->setTitle($text);
                        // output
                        $like->setDescription($comment->getExcerpt());
                    }
                }
            } else {
                // response like
                if (isset($responses[$like->objectID])) {
                    $response = $responses[$like->objectID];
                    $comment = $comments[$response->commentID];
                    if (isset($todos[$comment->objectID]) && $todos[$comment->objectID]->canRead()) {
                        $like->setIsAccessible();
                        // short output
                        $text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.de.julian-pfeil.todolist.todoComment.response', [
                            'responseAuthor' => $comment->userID ? $users[$response->userID] : null,
                            'response' => $response,
                            'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
                            'todo' => $todos[$comment->objectID],
                            'like' => $like,
                        ]);
                        $like->setTitle($text);
                        // output
                        $like->setDescription($response->getExcerpt());
                    }
                }
            }
        }
    }
}
