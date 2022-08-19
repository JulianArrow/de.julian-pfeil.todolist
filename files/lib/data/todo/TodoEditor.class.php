<?php

namespace todolist\data\todo;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit todos.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package WoltLabSuite\Core\Data\Todo
 *
 * @method static   Todo  create(array $parameters = [])
 * @method      Todo  getDecoratedObject()
 * @mixin       Todo
 */
class TodoEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Todo::class;
}
