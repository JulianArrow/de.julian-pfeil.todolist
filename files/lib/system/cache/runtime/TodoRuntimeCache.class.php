<?php

namespace todolist\system\cache\runtime;

use todolist\data\todo\Todo;
use todolist\data\todo\TodoList;
use wcf\system\cache\runtime\AbstractRuntimeCache;

/**
 * Runtime cache implementation for todos.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package WoltLabSuite\Core\System\Cache\Runtime
 *
 * @method  Todo[]    getCachedObjects()
 * @method  Todo      getObject($objectID)
 * @method  Todo[]    getObjects(array $objectIDs)
 */
class TodoRuntimeCache extends AbstractRuntimeCache
{
    /**
     * @inheritDoc
     */
    protected $listClassName = TodoList::class;
}
