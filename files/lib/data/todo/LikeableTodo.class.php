<?php

namespace todolist\data\todo;

use wcf\data\like\Like;
use wcf\data\like\object\AbstractLikeObject;
use wcf\data\reaction\object\IReactionObject;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\object\LikeUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Likeable object implementation for todos.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage data.todo
 */
class LikeableTodo extends AbstractLikeObject implements IReactionObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Todo::class;

    /**
     * @inheritDoc
     */
    public function getLanguageID()
    {
        return $this->getDecoratedObject()->languageID;
    }

    /**
     * @inheritDoc
     */
    public function getObjectID()
    {
        return $this->todoID;
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getSubject();
    }

    /**
     * @inheritDoc
     */
    public function getURL()
    {
        return LinkHandler::getInstance()->getLink('Todo', [
            'application' => 'todolist',
            'object' => $this->getDecoratedObject(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getUserID()
    {
        return $this->userID;
    }

    /**
     * @inheritDoc
     */
    public function sendNotification(Like $like)
    {
        if ($this->getDecoratedObject()->userID != WCF::getUser()->userID) {
            $notificationObject = new LikeUserNotificationObject($like);
            UserNotificationHandler::getInstance()->fireEvent('like', 'de.julian-pfeil.todolist.likeableTodo.notification', $notificationObject, [$this->getDecoratedObject()->userID], ['objectID' => $this->getDecoratedObject()->todoID]);
        }
    }

    /**
     * @inheritDoc
     */
    public function updateLikeCounter($cumulativeLikes)
    {
        $editor = new TodoEditor($this->getDecoratedObject());
        $editor->update(['cumulativeLikes' => $cumulativeLikes]);
    }
}
