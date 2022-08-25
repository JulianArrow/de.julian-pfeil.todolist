<?php
namespace todolist\system\comment\manager;

use todolist\data\todo\Todo;
use todolist\data\todo\TodoEditor;
use todolist\system\cache\runtime\TodoRuntimeCache;
use wcf\system\comment\manager\AbstractCommentManager;
use wcf\system\WCF;

/**
 * Comment manager implementation for todo.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package WoltLabSuite\Core\System\Comment\Manager
 */
class TodoCommentManager extends AbstractCommentManager
{
    /**
     * @inheritDoc
     */
    protected $permissionAdd = 'user.todolist.comments.canAddComments';

    /**
     * @inheritDoc
     */
    protected $permissionAddWithoutModeration = 'user.todolist.comments.canAddCommentsWithoutModeration';

    /**
     * @inheritDoc
     */
    protected $permissionCanModerate = 'mod.todolist.comments.canModerateComments';

    /**
     * @inheritDoc
     */
    protected $permissionDelete = 'user.todolist.comments.canDeleteComments';

    /**
     * @inheritDoc
     */
    protected $permissionEdit = 'user.todolist.comments.canEditComments';

    /**
     * @inheritDoc
     */
    protected $permissionModDelete = 'mod.todolist.comments.canDeleteComments';

    /**
     * @inheritDoc
     */
    protected $permissionModEdit = 'mod.todolist.comments.canEditComments';

    /**
     * @inheritDoc
     */
    public function getLink($objectTypeID, $objectID)
    {
        return TodoRuntimeCache::getInstance()->getObject($objectID)->getLink();
    }

    /**
     * @inheritDoc
     */
    public function isAccessible($objectID, $validateWritePermission = false)
    {
        return TodoRuntimeCache::getInstance()->getObject($objectID) !== null;
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
}
