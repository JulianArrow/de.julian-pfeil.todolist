<?php

namespace todolist\system\user\activity\event;

use todolist\data\todo\list\ViewableTodoList;
use wcf\data\comment\CommentList;
use wcf\system\SingletonFactory;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\WCF;

/**
 * Class TodoCommentUserActivityEvent
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.user.activity.event
 */
class TodoCommentUserActivityEvent extends SingletonFactory implements IUserActivityEvent
{
    /**
     * @inheritDoc
     */
    public function prepare(array $events)
    {
        $commentIDs = [];
        foreach ($events as $event) {
            $commentIDs[] = $event->objectID;
        }

        // fetch comments
        $commentList = new CommentList();
        $commentList->setObjectIDs($commentIDs);
        $commentList->readObjects();
        $comments = $commentList->getObjects();

        // fetch entries
        $todoIDs = $entries = [];
        foreach ($comments as $comment) {
            $todoIDs[] = $comment->objectID;
        }
        if (!empty($todoIDs)) {
            $todoList = new ViewableTodoList();
            $todoList->setObjectIDs($todoIDs);
            $todoList->readObjects();
            $entries = $todoList->getObjects();
        }

        // set message
        foreach ($events as $event) {
            if (isset($comments[$event->objectID])) {
                // short output
                $comment = $comments[$event->objectID];
                if (isset($entries[$comment->objectID])) {
                    $todo = $entries[$comment->objectID];

                    // check permissions
                    if (!$todo->canRead()) {
                        continue;
                    }
                    $event->setIsAccessible();

                    // add title
                    // todo eintrag in sprache auswerten
                    $text = WCF::getLanguage()->getDynamicVariable('todolist.todo.recentActivity.todoComment', ['todo' => $todo]);
                    $event->setTitle($text);

                    // add text
                    $event->setDescription($comment->getExcerpt());
                    continue;
                }
            }

            $event->setIsOrphaned();
        }
    }
}
