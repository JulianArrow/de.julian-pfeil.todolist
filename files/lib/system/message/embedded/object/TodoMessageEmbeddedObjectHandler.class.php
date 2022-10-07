<?php
namespace todolist\system\message\embedded\object;

use todolist\data\todo\TodoList;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\AbstractMessageEmbeddedObjectHandler;
use wcf\util\ArrayUtil;

/**
 * Message embedded object handler implementation for todos.
 * 
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class TodoMessageEmbeddedObjectHandler extends AbstractMessageEmbeddedObjectHandler {
    /**
     * @inheritDoc
     */
    public function loadObjects(array $objectIDs) {
        $todoList = new TodoList();
        $todoList->getConditionBuilder()->add('todo.todoID IN (?)', [$objectIDs]);
        $todoList->readObjects();
        return $todoList->getObjects();
    }
    
    /**
     * @inheritDoc
     */
    public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData) {
        if (!empty($embeddedData['todo'])) {
            $parsedTodoIDs = [];
            foreach ($embeddedData['todo'] as $attributes) {
                if (!empty($attributes[0])) {
                    $parsedTodoIDs = array_merge($parsedTodoIDs, ArrayUtil::toIntegerArray(explode(',', $attributes[0])));
                }
            }
            
            $todoIDs = array_unique(array_filter($parsedTodoIDs));
            if (!empty($todoIDs)) {
                $todoList = new TodoList();
                $todoList->getConditionBuilder()->add('todo.todoID IN (?)', [$todoIDs]);
                $todoList->readObjectIDs();
                
                return $todoList->getObjectIDs();
            }
        }
        
        return [];
    }
}
