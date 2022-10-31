<?php

namespace todolist\data\todo\list;

use todolist\data\todo\ViewableTodo;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\reaction\ReactionHandler;

/**
 * Represents a viewable list of todos.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage data.todo.list
 */
class ViewableTodoList extends TodoList
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = ViewableTodo::class;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        // get avatars
        if (!empty($this->sqlSelects)) {
            $this->sqlSelects .= ',';
        }
        $this->sqlSelects .= "user_avatar.*, user_table.*";
        $this->sqlJoins .= " LEFT JOIN wcf" . WCF_N ."_user user_table ON (user_table.userID = todo.userID)";
        $this->sqlJoins .= " LEFT JOIN wcf" . WCF_N ."_user_avatar user_avatar ON (user_avatar.avatarID = user_table.avatarID)";

        if (MODULE_LIKE) {
            if (!empty($this->sqlSelects)) {
                $this->sqlSelects .= ',';
            }
            $this->sqlSelects .= "like_object.cachedReactions";
            $this->sqlJoins .= " LEFT JOIN wcf" . WCF_N ."_like_object like_object ON (like_object.objectTypeID = " . ReactionHandler::getInstance()->getObjectType('de.julian-pfeil.todolist.likeableTodo')->objectTypeID . " AND like_object.objectID = todo.todoID)";
        }
    }

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        parent::readObjects();

        $userIDs = [];
        foreach ($this->objects as $todo) {
            if ($todo->userID) {
                $userIDs[] = $todo->userID;
            }
        }

        if (!empty($userIDs)) {
            UserProfileRuntimeCache::getInstance()->cacheObjectIDs($userIDs);
        }
    }

    /**
     * Reads the embedded objects of the link entries in the list.
     */
    public function readEmbeddedObjects()
    {
        if (!empty($this->embeddedObjectIDs)) {
            // load embedded objects
            MessageEmbeddedObjectManager::getInstance()->loadObjects('de.julian-pfeil.todolist.todo', $this->embeddedObjectIDs);
        }
    }
}
