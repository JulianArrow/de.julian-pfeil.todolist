<?php

namespace todolist\data\todo\list;

use todolist\system\label\object\TodoLabelObjectHandler;
use todolist\data\todo\ViewableTodo;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\reaction\ReactionHandler;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Represents a viewable list of todos.
 *
 * @author     Julian Pfeil <https://julian-pfeil.de>
 * @link    https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
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
        $this->sqlJoins .= " LEFT JOIN wcf" . WCF_N . "_user user_table ON (user_table.userID = links.userID)";
        $this->sqlJoins .= " LEFT JOIN wcf" . WCF_N . "_user_avatar user_avatar ON (user_avatar.avatarID = user_table.avatarID)";

        if (MODULE_LIKE) {
            if (!empty($this->sqlSelects)) {
                $this->sqlSelects .= ',';
            }
            $this->sqlSelects .= "like_object.cachedReactions";
            $this->sqlJoins .= " LEFT JOIN wcf" . WCF_N . "_like_object like_object ON (like_object.objectTypeID = " . ReactionHandler::getInstance()->getObjectType('de.julian-pfeil.todolist.likableTodo')->objectTypeID . " AND like_object.objectID = todo.todoID)";
        }
    }

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        parent::readObjects();

        $userIDs = $todoIDs = [];
        foreach ($this->objects as $todo) {
            if ($todo->userID) {
                $userIDs[] = $todo->userID;
            }

            if (defined('TODOLIST_LABELS_PLUGIN')) {
                if ($todo->hasLabels) {
                    $todoIDs[] = $todo->todoID;
                }
            }
        }

        if (!empty($userIDs)) {
            UserProfileRuntimeCache::getInstance()->cacheObjectIDs($userIDs);
        }

        if (defined('TODOLIST_LABELS_PLUGIN') && !empty($todoIDs)) {
            $assignedLabels = TodoLabelObjectHandler::getInstance()->getAssignedLabels($todoIDs);
            foreach ($assignedLabels as $todoID => $labels) {
                foreach ($labels as $label) {
                    $this->objects[$todoID]->addLabel($label);
                }
            }
        }
    }

    /**
     * Reads the embedded objects of the link entries in the list.
     */
    public function readEmbeddedObjects()
    {
        if (!empty($this->embeddedObjectIDs)) {
            // load embedded objects
            MessageEmbeddedObjectManager::getInstance()->loadObjects('de.pehbeh.links.linkEntry', $this->embeddedObjectIDs);
        }
    }
}
