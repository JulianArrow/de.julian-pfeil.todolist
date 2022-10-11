<?php

namespace todolist\data\todo;

use todolist\data\todo\list\ViewableTodoList;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\WCF;

/**
 * Object type provider for todos.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage data.todo
 */
class LikeableTodoProvider extends TodoProvider implements ILikeObjectTypeProvider, IViewableLikeProvider
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = LikeableTodo::class;

    /**
     * @inheritdoc
     */
    public function checkPermissions(ILikeObject $object)
    {
        $todo = new Todo($object->getObjectID());
        if (!$todo->todoID) {
            return false;
        }

        return $todo->canRead();
    }

    /**
     * @inheritDoc
     */
    public function canLike(ILikeObject $object)
    {
        return $this->canViewLikes($object);
    }

    /**
     * @inheritDoc
     */
    public function canViewLikes(ILikeObject $object)
    {
        return $object->todoID && $object->canRead();
    }

    /**
     * @inheritDoc
     */
    public function prepare(array $likes)
    {
        $todoIDs = [];
        foreach ($likes as $like) {
            $todoIDs[] = $like->objectID;
        }

        // get todos
        $todoList = new ViewableTodoList();
        $todoList->setObjectIDs($todoIDs);
        $todoList->readObjects();
        $todos = $todoList->getObjects();

        // set message
        foreach ($likes as $like) {
            if (isset($todos[$like->objectID])) {
                $todo = $todos[$like->objectID];
            // check permissions
                if (!WCF::getSession()->getPermission('user.todolist.general.canViewTodoList')) {
                    continue;
                }

                $like->setIsAccessible();
            // short output
                $text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.de.julian-pfeil.todolist.likeableTodo', ['todo' => $todo, 'like' => $like]);
                $like->setTitle($text);
            // output
                $like->setDescription($todo->getExcerpt());
            }
        }
    }
}
