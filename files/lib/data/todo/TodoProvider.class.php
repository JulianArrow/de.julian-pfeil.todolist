<?php
namespace todolist\data\todo;
use wcf\data\object\type\AbstractObjectTypeProvider;

/**
 * Object type provider implementation for todos.
 * 
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package WoltLabSuite\Core\Page
 */
class TodoProvider extends AbstractObjectTypeProvider {
    /**
     * @inheritDoc
     */
    public $className = Todo::class;
    
    /**
     * @inheritDoc
     */
    public $listClassName = TodoList::class;
}
