<?php

namespace todolist\system\cache\builder;

use wcf\system\cache\builder\AbstractCacheBuilder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\category\CategoryHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Caching available label groups for the to-do list categories.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class TodoCategoryLabelCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritdoc
     */
    protected function rebuild(array $parameters)
    {
        $data = [];

        $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.label.objectType', 'de.julian-pfeil.todolist.todo.category')->objectTypeID;
        $categoryObjectTypeID = CategoryHandler::getInstance()->getObjectTypeByName('de.julian-pfeil.todolist.todo.category')->objectTypeID;

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('objectTypeID = ?', [$objectTypeID]);
        $conditionBuilder->add('objectID IN (SELECT categoryID FROM wcf' . WCF_N . '_category WHERE objectTypeID = ?)', [$categoryObjectTypeID]);

        $sql = "SELECT groupID, objectID
                FROM   wcf" . WCF_N . "_label_group_to_object
                       " . $conditionBuilder;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditionBuilder->getParameters());

        while ($row = $statement->fetchArray()) {
            if (!isset($data[$row['objectID']])) {
                $data[$row['objectID']] = [];
            }

            $data[$row['objectID']][] = $row['groupID'];
        }

        return $data;
    }
}
