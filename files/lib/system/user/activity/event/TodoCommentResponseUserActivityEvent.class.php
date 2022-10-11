<?php

namespace todolist\system\user\activity\event;

use todolist\data\todo\list\TodoList;
use wcf\data\comment\CommentList;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\user\UserList;
use wcf\system\SingletonFactory;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\WCF;

/**
 * Class TodoCommentResponseUserActivityEvent
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.user.activity.event
 */
class TodoCommentResponseUserActivityEvent extends SingletonFactory implements IUserActivityEvent
{
    /**
     * @inheritDoc
     */
    public function prepare(array $events)
    {
        $responseIDs = [];
        foreach ($events as $event) {
            $responseIDs[] = $event->objectID;
        }

        // fetch responses
        $responseList = new CommentResponseList();
        $responseList->setObjectIDs($responseIDs);
        $responseList->readObjects();
        $responses = $responseList->getObjects();

        // fetch comments
        $commentIDs = $comments = [];
        foreach ($responses as $response) {
            $commentIDs[] = $response->commentID;
        }
        if (!empty($commentIDs)) {
            $commentList = new CommentList();
            $commentList->setObjectIDs($commentIDs);
            $commentList->readObjects();
            $comments = $commentList->getObjects();
        }

        // fetch entries
        $todoIDs = $entries = [];
        foreach ($comments as $comment) {
            $todoIDs[] = $comment->objectID;
        }
        if (!empty($todoIDs)) {
            $todoList = new TodoList();
            $todoList->setObjectIDs($todoIDs);
            $todoList->readObjects();
            $entries = $todoList->getObjects();
        }

        // fetch users
        $userIDs = $user = [];
        foreach ($comments as $comment) {
            $userIDs[] = $comment->userID;
        }
        if (!empty($userIDs)) {
            $userList = new UserList();
            $userList->setObjectIDs($userIDs);
            $userList->readObjects();
            $users = $userList->getObjects();
        }

        // set message
        foreach ($events as $event) {
            if (isset($responses[$event->objectID])) {
                $response = $responses[$event->objectID];
                $comment = $comments[$response->commentID];
                if (isset($entries[$comment->objectID]) && isset($users[$comment->userID])) {
                    $todo = $entries[$comment->objectID];

                    // check permissions
                    if (!$todo->canRead()) {
                        continue;
                    }
                    $event->setIsAccessible();

                    // title
                    // todo eintrag in sprache auswerten
                    $text = WCF::getLanguage()->getDynamicVariable('todolist.general.recentActivity.todoCommentResponse', [
                        'commentAuthor' => $users[$comment->userID],
                        'todo' => $todo
                    ]);
                    $event->setTitle($text);

                    // description
                    $event->setDescription($response->getExcerpt());
                    continue;
                }
            }

            $event->setIsOrphaned();
        }
    }
}
