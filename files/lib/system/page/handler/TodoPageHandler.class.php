<?php

namespace todolist\system\page\handler;

use todolist\data\todo\ViewableTodoList;
use todolist\system\cache\runtime\ViewableTodoRuntimeCache;
use wcf\data\page\Page;
use wcf\data\user\online\UserOnline;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\page\handler\AbstractLookupPageHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\page\handler\TOnlineLocationPageHandler;
use wcf\system\WCF;

/**
 * Page handler implementation for todo page.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.page.handler
 */
class TodoPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler
{
    use TOnlineLocationPageHandler;

    /**
     * @inheritDoc
     */
    public function getLink($objectID)
    {
        return ViewableTodoRuntimeCache::getInstance()->getObject($objectID)->getLink();
    }

    /**
     * Returns the textual description if a user is currently online viewing this page.
     *
     * @see IOnlineLocationPageHandler::getOnlineLocation()
     *
     * @param   Page        $page       visited page
     * @param   UserOnline  $user       user online object with request data
     * @return  string
     */
    public function getOnlineLocation(Page $page, UserOnline $user)
    {
        if ($user->pageObjectID === null) {
            return '';
        }

        $todo = ViewableTodoRuntimeCache::getInstance()->getObject($user->pageObjectID);
        if ($todo === null) {
            return '';
        }

        return WCF::getLanguage()->getDynamicVariable('wcf.page.onlineLocation.' . $page->identifier, ['todo' => $todo]);
    }

    /**
     * @inheritDoc
     */
    public function isValid($objectID = null)
    {
        return ViewableTodoRuntimeCache::getInstance()->getObject($objectID) !== null;
    }

    /**
     * @inheritDoc
     */
    public function lookup($searchString)
    {
        $conditionBuilder = new PreparedStatementConditionBuilder(false, 'OR');
        $conditionBuilder->add('todo.todoName LIKE ?', ['%' . $searchString . '%']);
        $conditionBuilder->add('todo.description LIKE ?', ['%' . $searchString . '%']);

        $todoList = new ViewableTodoList();
        $todoList->getConditionBuilder()->add($conditionBuilder, $conditionBuilder->getParameters());
        $todoList->readObjects();

        $results = [];
        foreach ($todoList as $todo) {
            $results[] = [
                'image' => 'fa-pencil',
                'link' => $todo->getLink(),
                'objectID' => $todo->todoID,
                'title' => $todo->getTitle(),
            ];
        }

        return $results;
    }

    /**
     * Prepares fetching all necessary data for the textual description if a user is currently online
     * viewing this page.
     *
     * @see IOnlineLocationPageHandler::prepareOnlineLocation()
     *
     * @param   Page        $page       visited page
     * @param   UserOnline  $user       user online object with request data
     */
    public function prepareOnlineLocation(Page $page, UserOnline $user)
    {
        if ($user->pageObjectID !== null) {
            ViewableTodoRuntimeCache::getInstance()->cacheObjectID($user->pageObjectID);
        }
    }
}
