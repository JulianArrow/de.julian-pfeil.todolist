<?php

/**
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 */

use wcf\data\category\CategoryEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

// add default todo category
$categoryObjectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.category', 'de.julian-pfeil.todolist.todo.category');
CategoryEditor::create([
    'objectTypeID' => $categoryObjectTypeID,
    'title' => 'Default Category',
    'time' => TIME_NOW,
]);

$conditionBuilder = new PreparedStatementConditionBuilder();
$conditionBuilder->add('objectTypeID = ?', [$categoryObjectTypeID]);
$sql = "SELECT  categoryID
        FROM    wcf" . WCF_N . "_category category
        " . $conditionBuilder . "
        ORDER BY category.categoryID DESC";
$limit = 1;
$statement = WCF::getDB()->prepare($sql, $limit);
$statement->execute($conditionBuilder->getParameters());
$categoryID = $statement->fetchSingleColumn();

// set every todos category to default
$sql = "UPDATE  todolist" . WCF_N . "_todo todo
        SET     todo.categoryID = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([$categoryID]);

// set lastEditTime = time
$sql = "UPDATE  todolist" . WCF_N . "_todo todo
        SET     todo.lastEditTime = todo.time";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
