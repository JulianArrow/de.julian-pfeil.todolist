<?php

namespace todolist\system\user\notification\object\type;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;
use wcf\system\user\notification\object\CommentUserNotificationObject;
use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;
use wcf\system\user\notification\object\type\ICommentUserNotificationObjectType;
use wcf\system\WCF;

/**
 * Represents a comment notification object type.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     License for Commercial Plugins <https://julian-pfeil.de/lizenz/>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.user.notification.object.type
 */
class TodoCommentUserNotificationObjectType extends AbstractUserNotificationObjectType implements ICommentUserNotificationObjectType
{
    /**
     * @inheritDoc
     */
    protected static $decoratorClassName = CommentUserNotificationObject::class;

    /**
     * @inheritDoc
     */
    protected static $objectClassName = Comment::class;

    /**
     * @inheritDoc
     */
    protected static $objectListClassName = CommentList::class;

    /**
     * @inheritDoc
     */
    public function getOwnerID($objectID)
    {
        $sql = "SELECT		todo.userID
                FROM		wcf1_comment comment
                LEFT JOIN	todolist" . WCF_N . "_todo todo
                ON			(todo.todoID = comment.objectID)
                WHERE		comment.commentID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$objectID]);

        return $statement->fetchSingleColumn() ?: 0;
    }
}
