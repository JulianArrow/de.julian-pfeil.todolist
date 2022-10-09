<?php

namespace todolist\system\log\modification;

use todolist\data\todo\Todo;
use todolist\data\todo\TodoList;
use todolist\data\modification\log\TodoModificationLog;
use wcf\data\label\Label;
use wcf\system\log\modification\AbstractExtendedModificationLogHandler;

/**
 * Handles todo modification logs.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class TodoModificationLogHandler extends AbstractExtendedModificationLogHandler
{
    /**
     * @inheritDoc
     */
    protected $objectTypeName = 'de.julian-pfeil.todolist.todo';

    /**
     * Adds an todo modification log entry.
     */
    public function add(Todo $todo, $action, array $additionalData = [])
    {
        $this->createLog($action, $todo->todoID, null, $additionalData);
    }

    /**
     * Adds a log entry for todo edit.
     */
    public function edit(Todo $todo, $reason = '')
    {
        $this->add($todo, 'edit', ['reason' => $reason]);
    }

    /**
     * Adds a log entry for changed labels.
     */
    public function setLabel(Todo $todo, Label $label)
    {
        $this->add($todo, 'setLabel', ['label' => $label]);
    }

    /**
     * Adds a log entry for todo markAsDone / markAsUndone.
     */
    public function markAsDone(Todo $todo)
    {
        $this->add($todo, 'markAsDone');
    }

    public function markAsUndone(Todo $todo)
    {
        $this->add($todo, 'markAsUndone');
    }

    /**
     * @inheritDoc
     */
    public function getAvailableActions()
    {
        return ['edit', 'markAsDone', 'markAsUndone', 'setLabel'];
    }

    /**
     * @inheritDoc
     */
    public function processItems(array $items)
    {
        $todoIDs = [];
        foreach ($items as &$item) {
            $todoIDs[] = $item->objectID;
            $item = new TodoModificationLog($item);
        }
        unset($item);
        if (!empty($todoIDs)) {
            $todoList = new TodoList();
            $todoList->setObjectIDs($todoIDs);
            $todoList->readObjects();
            $todos = $todoList->getObjects();
            foreach ($items as $item) {
                if (isset($todos[$item->objectID])) {
                    $item->setTodo($todos[$item->objectID]);
                }
            }
        }

        return $items;
    }
}
