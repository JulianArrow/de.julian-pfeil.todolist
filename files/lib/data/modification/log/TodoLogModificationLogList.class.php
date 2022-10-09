<?php

namespace todolist\data\modification\log;

use todolist\data\todo\Todo;
use todolist\system\log\modification\TodoModificationLogHandler;
use wcf\data\modification\log\ModificationLogList;
use wcf\system\WCF;

/**
 * Represents a list of modification logs for todo log page.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class TodoLogModificationLogList extends ModificationLogList
{
    /**
     * todo data
     */
    public $todoObjectTypeID = 0;
    public $todo;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
// get object types
        $this->todoObjectTypeID = TodoModificationLogHandler::getInstance()->getObjectType()->objectTypeID;
    }

    /**
     * @inheritDoc
     */
    public function countObjects()
    {
        $sql = "SELECT		COUNT(modification_log.logID) AS count
                FROM		wcf" . WCF_N . "_modification_log modification_log
                WHERE		modification_log.objectTypeID = ? AND modification_log.objectID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->todoObjectTypeID, $this->todo->todoID]);
        $count = 0;
        while ($row = $statement->fetchArray()) {
            $count += $row['count'];
        }

        return $count;
    }

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        $sql = "SELECT		user_avatar.*, user_table.email, user_table.disableAvatar, user_table.enableGravatar, user_table.gravatarFileExtension, modification_log.*
                FROM		wcf" . WCF_N . "_modification_log modification_log
                LEFT JOIN	wcf" . WCF_N . "_user user_table
                ON			(user_table.userID = modification_log.userID)
                LEFT JOIN	wcf" . WCF_N . "_user_avatar user_avatar
                ON			(user_avatar.avatarID = user_table.avatarID)
                WHERE		modification_log.objectTypeID = ? AND modification_log.objectID = ?
                " . (!empty($this->sqlOrderBy) ? "ORDER BY " . $this->sqlOrderBy : '');
        $statement = WCF::getDB()->prepareStatement($sql, $this->sqlLimit, $this->sqlOffset);
        $statement->execute([$this->todoObjectTypeID, $this->todo->todoID]);
        $this->objects = $statement->fetchObjects(($this->objectClassName ?: $this->className));
// use table index as array index
        $objects = [];
        foreach ($this->objects as $object) {
            $objectID = $object->{$this->getDatabaseTableIndexName()};
            $objects[$objectID] = $object;
            $this->indexToObject[] = $objectID;
        }
        $this->objectIDs = $this->indexToObject;
        $this->objects = $objects;
        $versionIDs = [];
        foreach ($this->objects as &$object) {
            $object = new TodoModificationLog($object);
        }
        unset($object);
    }

    /**
     * Initializes the todo's log.
     */
    public function setTodo(Todo $todo)
    {
        $this->todo = $todo;
    }
}
