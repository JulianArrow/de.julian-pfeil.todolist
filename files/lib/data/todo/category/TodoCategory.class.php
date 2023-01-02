<?php

namespace todolist\data\todo\category;

use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\IAccessibleObject;
use wcf\data\ITitledLinkObject;
use wcf\data\user\User;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Class TodoCategory
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage data.todo.category
 */

class TodoCategory extends AbstractDecoratedCategory implements IAccessibleObject, ITitledLinkObject
{
    /**
     * object type name of the todo categories
     *
     * @var        string
     */
    public const OBJECT_TYPE_NAME = 'de.julian-pfeil.todolist.todo.category';

    /**
     * subscribed categories field name
     */
    public const USER_STORAGE_SUBSCRIBED_CATEGORIES = self::class . "\0subscribedCategories";

    /**
     * ACL permissions of this category grouped by the id of the user they belong to
     *
     * @var        array
     */
    protected $userPermissions = [];

    /**
     * ids of subscribed todo categories
     */
    protected static $subscribedCategories;

    /**
     * Returns a list with ids of accessible categories.
     *
     * @param array $permissions
     *
     * @return        int
     * @throws SystemException
     */
    public static function getAccessibleCategoryIDs(array $permissions = [])
    {
        $categoryIDs = [];
        foreach (CategoryHandler::getInstance()->getCategories(self::OBJECT_TYPE_NAME) as $category) {
            $result = true;
            $category = new self($category);
            foreach ($permissions as $permission) {
                $result = $result && $category->getPermission($permission);
            }

            $result = $result && $category->canView();

            if ($result) {
                $categoryIDs[] = $category->categoryID;
            }
        }

        return $categoryIDs;
    }

    /**
     * Returns subscribed category IDs.
     */
    public static function getSubscribedCategoryIDs()
    {
        if (self::$subscribedCategories === null) {
            self::$subscribedCategories = [];

            if (WCF::getUser()->userID) {
                $data = UserStorageHandler::getInstance()->getField(self::USER_STORAGE_SUBSCRIBED_CATEGORIES);

                // cache does not exist or is outdated
                if ($data === null) {
                    $objectTypeID = UserObjectWatchHandler::getInstance()->getObjectTypeID('de.julian-pfeil.todolist.todo.category');

                    $sql = "SELECT	objectID
							FROM	wcf1_user_object_watch
							WHERE	objectTypeID = ? AND userID = ?";
                    $statement = WCF::getDB()->prepare($sql);
                    $statement->execute([$objectTypeID, WCF::getUser()->userID]);
                    self::$subscribedCategories = $statement->fetchAll(\PDO::FETCH_COLUMN);

                    // update storage data
                    UserStorageHandler::getInstance()->update(WCF::getUser()->userID, self::USER_STORAGE_SUBSCRIBED_CATEGORIES, \serialize(self::$subscribedCategories));
                } else {
                    self::$subscribedCategories = \unserialize($data);
                }
            }
        }

        return self::$subscribedCategories;
    }

    /**
     * Returns the link to the object.
     *
     * @return        string
     * @throws SystemException
     */
    public function getLink()
    {
        return LinkHandler::getInstance()->getLink('TodoList', [
            'application' => 'todolist',
            'categoryID' => $this->categoryID,
        ]);
    }

    /**
     * Returns the title of the object.
     *
     * @return        string
     */
    public function getTitle()
    {
        return WCF::getLanguage()->get($this->title);
    }

    /**
     * Returns true if the active user has subscribed to this category.
     */
    public function isSubscribed()
    {
        return \in_array($this->categoryID, self::getSubscribedCategoryIDs());
    }

    /**
     * @inheritDoc
     */
    public function isAccessible(?User $user = null)
    {
        if ($this->getObjectType()->objectType != self::OBJECT_TYPE_NAME) {
            return false;
        }

        return true;
    }

    /**
     * checks user permissions for viewing this category
     */
    public function canView(?User $user = null)
    {
        if ($user === null) {
            $user = WCF::getUser();
        }

        if (!$user->userID) {
            return false;
        }

        if (!$this->isAccessible()) {
            return false;
        }

        if (WCF::getSession()->getPermission('user.todolist.general.canViewEveryCategory')) {
            return true;
        }

        return $this->getPermission('canViewCategory', $user);
    }

    /**
     * checks if user is logged in and checks the categories object type
     */
    public function checkLogInAndAccess()
    {
        if (!WCF::getUser()->userID || !$this->isAccessible()) {
            return false;
        }

        return true;
    }

    /**
     * checks user permissions for editing a todo in this category
     */
    public function canEditTodo()
    {
        if (!$this->checkLogInAndAccess()) {
            return false;
        }

        if (WCF::getSession()->getPermission('mod.todolist.general.canEditTodoInEveryCategory')) {
            return true;
        }

        // check permissions
        return $this->getPermission('canEditTodo', WCF::getUser());
    }

    /**
     * checks user permissions for editing a todo in this category
     */
    public function canEditOwnTodo()
    {
        if (!$this->checkLogInAndAccess()) {
            return false;
        }

        if (WCF::getSession()->getPermission('user.todolist.general.canEditOwnTodoInEveryCategory')) {
            return true;
        }

        // check permissions
        return $this->getPermission('canEditOwnTodo', WCF::getUser());
    }

    /**
     * checks user permissions for deleting an own todo in this category
     */
    public function canDeleteOwnTodo()
    {
        if (!$this->checkLogInAndAccess()) {
            return false;
        }

        if (WCF::getSession()->getPermission('user.todolist.general.canDeleteOwnTodoInEveryCategory')) {
            return true;
        }

        // check permissions
        return $this->getPermission('canDeleteOwnTodo', WCF::getUser());
    }

    /**
     * checks user permissions for deleting a todo in this category
     */
    public function canDeleteTodo()
    {
        if (!$this->checkLogInAndAccess()) {
            return false;
        }

        if (WCF::getSession()->getPermission('mod.todolist.general.canDeleteTodoInEveryCategory')) {
            return true;
        }

        // check permissions
        return $this->getPermission('canDeleteTodo', WCF::getUser());
    }

    /**
     * checks user permissions for adding a todo in this category
     */
    public function canAddTodo()
    {
        if (!$this->checkLogInAndAccess()) {
            return false;
        }

        if (WCF::getSession()->getPermission('user.todolist.general.canAddTodoInEveryCategory')) {
            return true;
        }

        // check permissions
        return $this->getPermission('canAddTodo', WCF::getUser());
    }
}
