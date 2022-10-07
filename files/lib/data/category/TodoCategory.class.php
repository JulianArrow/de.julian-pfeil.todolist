<?php

namespace todolist\data\category;

use todolist\system\cache\builder\TodoCategoryLabelCacheBuilder;

use wcf\system\label\LabelHandler;
use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\IAccessibleObject;
use wcf\data\ITitledLinkObject;
use wcf\data\user\User;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Class TodoCategory
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @method static TodoCategory|null        getCategory($categoryID)
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
     * ACL permissions of this category grouped by the id of the user they belong to
     *
     * @var        array
     */
    protected $userPermissions = [];

    /**
     * Returns a list with ids of accessible categories.
     *
     * @param array $permissions
     *
     * @return        int
     * @throws SystemException
     */
    public static function getAccessibleCategoryIDs(array $permissions = ['canViewCategory'])
    {
        $categoryIDs = [];
        foreach (CategoryHandler::getInstance()->getCategories(self::OBJECT_TYPE_NAME) as $category) {
            $result = true;
            $category = new self($category);
            foreach ($permissions as $permission) {
                $result = $result && $category->getPermission($permission);
            }

            if ($result) {
                $categoryIDs[] = $category->categoryID;
            }
        }

        return $categoryIDs;
    }
    
    /**
     * Returns the label groups available for todos in the category.
     */
    public function getLabelGroups($permission = 'canViewLabel')
    {
        $labelGroups = [];

        $labelGroupsToCategories = TodoCategoryLabelCacheBuilder::getInstance()->getData();
        if (isset($labelGroupsToCategories[$this->categoryID])) {
            $labelGroups = LabelHandler::getInstance()->getLabelGroups($labelGroupsToCategories[$this->categoryID], true, $permission);
        }

        return $labelGroups;
    }
    
    /**
     * Returns the label groups for all accessible categories.
     */
    public static function getAccessibleLabelGroups($permission = 'canViewLabel')
    {
        $labelGroupsToCategories = TodoCategoryLabelCacheBuilder::getInstance()->getData();
        $accessibleCategoryIDs = self::getAccessibleCategoryIDs();

        $groupIDs = [];
        foreach ($labelGroupsToCategories as $categoryID => $__groupIDs) {
            if (\in_array($categoryID, $accessibleCategoryIDs)) {
                $groupIDs = \array_merge($groupIDs, $__groupIDs);
            }
        }
        if (empty($groupIDs)) {
            return [];
        }

        return LabelHandler::getInstance()->getLabelGroups(\array_unique($groupIDs), true, $permission);
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
            'categoryID' => $this->categoryID
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
     * @inheritDoc
     */
    public function isAccessible(User $user = null)
    {
        if ($this->getObjectType()->objectType != self::OBJECT_TYPE_NAME) {
            return false;
        }

        // check permissions
        return $this->getPermission('canViewCategory', $user);
    }

    /**
     * checks user permissions for viewing this category
     */
    public function canView()
    {
        if (!WCF::getUser()->userID) {
            return false;
        }

        if (WCF::getSession()->getPermission('user.todolist.general.canViewEveryCategory')) {
            return true;
        }

        // check permissions
        if (WCF::getUser()) {
            return $this->isAccessible(WCF::getUser());
        }

        return $this->isAccessible();
    }

    /**
     * checks if user is logged in and checks the categories object type
     */
    public function checkLogInAndObjectType() {
        if (!WCF::getUser()->userID || $this->getObjectType()->objectType != self::OBJECT_TYPE_NAME) {
            return false;
        }

        return true;
    }

    /**
     * checks user permissions for editing a todo in this category
     */
    public function canEditTodo()
    {
        if (!$this->checkLogInAndObjectType()) {
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
        if (!$this->checkLogInAndObjectType()) {
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
        if (!$this->checkLogInAndObjectType()) {
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
        if (!$this->checkLogInAndObjectType()) {
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
        if (!$this->checkLogInAndObjectType()) {
            return false;
        }
        
        if (WCF::getSession()->getPermission('user.todolist.general.canAddTodoInEveryCategory')) {
            return true;
        }
        
        // check permissions
        return $this->getPermission('canAddTodo', WCF::getUser());
    }
}