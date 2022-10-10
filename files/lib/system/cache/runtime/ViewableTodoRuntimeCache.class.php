<?php

namespace todolist\system\cache\runtime;

use todolist\data\todo\list\ViewableTodoList;
use wcf\system\cache\runtime\AbstractRuntimeCache;

/**
 * Runtime cache implementation for todos.
 *
 * @author     Julian Pfeil <https://julian-pfeil.de>
 * @link    https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.cache.runtime
 */
class ViewableTodoRuntimeCache extends AbstractRuntimeCache
{
    /**
     * @inheritDoc
     */
    protected $listClassName = ViewableTodoList::class;
}