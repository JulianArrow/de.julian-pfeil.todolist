<?php

namespace todolist\system\user\activity\event;

use todolist\data\todo\TodoList;
use wcf\system\SingletonFactory;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\WCF;

/**
 * Class TodoUserActivityEvent
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class TodoUserActivityEvent extends SingletonFactory implements IUserActivityEvent
{
    /**
     * @inheritDoc
     */
    public function prepare(array $events)
    {
        $objectIDs = [];
        foreach ($events as $event) {
            $objectIDs[] = $event->objectID;
        }

        // fetch entries
        $todoList = new TodoList();
        $todoList->setObjectIDs($objectIDs);
        $todoList->readObjects();
        $entries = $todoList->getObjects();

        // set message
        foreach ($events as $event) {
            if (isset($entries[$event->objectID])) {
                if (!$entries[$event->objectID]->canRead()) {
                    continue;
                }
                $event->setIsAccessible();

                // title
                // todo eintrag in sprachvariable verlinken
                $text = WCF::getLanguage()->getDynamicVariable('todolist.general.recentActivity.todo', ['todo' => $entries[$event->objectID]]);
                $event->setTitle($text);

                // description
                $event->setDescription($entries[$event->objectID]->getExcerpt());
            } else {
                $event->setIsOrphaned();
            }
        }
    }
}