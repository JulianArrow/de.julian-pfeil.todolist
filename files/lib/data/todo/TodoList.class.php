<?php

namespace todolist\data\todo;

use wcf\data\DatabaseObjectList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\reaction\ReactionHandler;

/**
 * Represents a list of todos.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package WoltLabSuite\Core\Data\Todo
 *
 * @method  Todo      current()
 * @method  Todo[]    getObjects()
 * @method  Todo|null search($objectID)
 * @property    Todo[]    $objects
 */
class TodoList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        if (MODULE_TODOLIST_REACTIONS) {
            // reactions
            if (!empty($this->sqlSelects)) {
                $this->sqlSelects .= ',';
            }
            $this->sqlSelects .= "like_object.cachedReactions";
            $this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_like_object like_object ON (like_object.objectTypeID = ".ReactionHandler::getInstance()->getObjectType('de.julian-pfeil.todolist.likeableTodo')->objectTypeID." AND like_object.objectID = todo.todoID)";
        }
    }

    public function readObjects()
    {
        parent::readObjects();

        UserProfileRuntimeCache::getInstance()->cacheObjectIDs(\array_unique(\array_filter(\array_column(
            $this->objects,
            'userID'
        ))));
    }
}
