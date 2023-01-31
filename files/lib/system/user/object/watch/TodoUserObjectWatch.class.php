<?php

namespace todolist\system\user\object\watch;

use todolist\data\todo\Todo;
use wcf\data\object\type\AbstractObjectTypeProcessor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\user\object\watch\IUserObjectWatch;
use wcf\system\user\storage\UserStorageHandler;

/**
 * Implementation of IUserObjectWatch for watched categories.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.user.object.watch
 */
class TodoUserObjectWatch extends AbstractObjectTypeProcessor implements IUserObjectWatch
{
    /**
     * @inheritDoc
     */
    public function validateObjectID($objectID)
    {
        $todo = new Todo($objectID);
        if (!$todo->todoID) {
            throw new IllegalLinkException();
        }
        if (!$todo->canRead()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function resetUserStorage(array $userIDs)
    {
        UserStorageHandler::getInstance()->reset($userIDs, Todo::USER_STORAGE_SUBSCRIBED_TODOS);
    }
}
