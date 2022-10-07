<?php

namespace todolist\data\todo;

use todo\data\category\TodoCategory;

use wcf\data\tag\Tag;
use wcf\system\tagging\TagEngine;
use wcf\system\tagging\TTaggedObjectList;
use wcf\system\WCF;

/**
 * Represents a tagged-list for todos.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class TaggedTodoList extends TodoList
{
    use TTaggedObjectList;

    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'todo.time DESC';

    /**
     * @var Tag[]
     */
    public $tags;

    /**
     * Creates a new TaggedTodoList object.
     */
    public function __construct($tags)
    {
        TodoList::__construct();

        $this->tags = ($tags instanceof Tag) ? [$tags] : $tags;

        $this->getConditionBuilder()->add('tag_to_object.objectTypeID = ? AND tag_to_object.tagID IN (?)', [
            TagEngine::getInstance()->getObjectTypeID('de.julian-pfei.todolist.todo'),
            TagEngine::getInstance()->getTagIDs($this->tags),
        ]);
        $this->getConditionBuilder()->add('todos.todoID = tag_to_object.objectID');

        $accessibleCategoryIDs = TodoCategory::getAccessibleCategoryIDs();
        if (!empty($accessibleCategoryIDs)) {
            $this->getConditionBuilder()->add('todos.categoryID IN (?)', [$accessibleCategoryIDs]);
        } else {
            $this->getConditionBuilder()->add('1=0');
        }
    }

    /**
     * @inheritDoc
     */
    public function countObjects()
    {
        $sql = "SELECT  COUNT(*)
                FROM    (
                    SELECT   tag_to_object.objectID
                    FROM     wcf" . WCF_N . "_tag_to_object tag_to_object,
                             todolist" . WCF_N . "_todo todos
                              " . $this->sqlConditionJoins . "
                             " . $this->getConditionBuilder() . "
                    GROUP BY tag_to_object.objectID
                    HAVING   COUNT(tag_to_object.objectID) = ?
                ) AS t";
        $statement = WCF::getDB()->prepareStatement($sql);

        $parameters = $this->getConditionBuilder()->getParameters();
        $parameters[] = \count($this->tags);
        $statement->execute($parameters);

        return $statement->fetchSingleColumn();
    }

    /**
     * @inheritDoc
     */
    public function readObjectIDs()
    {
        $sql = "SELECT tag_to_object.objectID
                FROM   wcf" . WCF_N . "_tag_to_object tag_to_object,
                       todolist" . WCF_N . "_todo todos
                       " . $this->sqlConditionJoins . "
                       " . $this->getConditionBuilder() . "
                       " . $this->getGroupByFromOrderBy('tag_to_object.objectID', $this->sqlOrderBy) . "
                HAVING COUNT(tag_to_object.objectID) = ?
                " . (!empty($this->sqlOrderBy) ? "ORDER BY " . $this->sqlOrderBy : '');
        $statement = WCF::getDB()->prepareStatement($sql, $this->sqlLimit, $this->sqlOffset);

        $parameters = $this->getConditionBuilder()->getParameters();
        $parameters[] = \count($this->tags);
        $statement->execute($parameters);
        $this->objectIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}
