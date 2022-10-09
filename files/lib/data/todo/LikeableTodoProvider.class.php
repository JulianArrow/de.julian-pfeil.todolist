<?php

namespace todolist\data\todo;

use wcf\data\like\object\ILikeObject;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\WCF;

/**
 * Object type provider for todos.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class LikeableTodoProvider extends TodoProvider implements ILikeObjectTypeProvider, IViewableLikeProvider
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = LikeableTodo::class;

    /**
     * @inheritDoc
     */
    public function checkPermissions(ILikeObject $object)
    {
        return $object->todoID && WCF::getSession()->getPermission('user.todolist.general.canSeeTodos');
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
        $todoList = new TodoList();
        $todoList->setObjectIDs($todoIDs);
        $todoList->readObjects();
        $todos = $todoList->getObjects();
// set message
        foreach ($likes as $like) {
            if (isset($todos[$like->objectID])) {
                $todo = $todos[$like->objectID];
            // check permissions
                if (!WCF::getSession()->getPermission('user.todolist.general.canSeeTodos')) {
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
