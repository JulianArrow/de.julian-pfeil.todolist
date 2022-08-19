<?php

namespace todolist\data\todo;

use wcf\data\DatabaseObjectList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;

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
    public function readObjects()
    {
        parent::readObjects();

        UserProfileRuntimeCache::getInstance()->cacheObjectIDs(\array_unique(\array_filter(\array_column(
            $this->objects,
            'userID'
        ))));
    }
}
