<?php

namespace todolist\system\search;

use todolist\data\todo\category\TodoCategory;
use todolist\data\todo\category\TodoCategoryNodeTree;
use todolist\data\todo\SearchResultTodoList;
use wcf\data\search\ISearchResultObject;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\search\AbstractSearchProvider;
use wcf\system\WCF;

/**
 * An implementation of ISearchableObjectType for searching in todos.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.search
 */
final class TodoSearch extends AbstractSearchProvider
{
    /**
     * data
     */
    private $todoCategoryID = 0;

    private $messageCache = [];

    /**
     * @inheritDoc
     */
    public function cacheObjects(array $objectIDs, ?array $additionalData = null): void
    {
        $todoList = new SearchResultTodoList();
        $todoList->setObjectIDs($objectIDs);
        $todoList->readObjects();
        foreach ($todoList->getObjects() as $todo) {
            $this->messageCache[$todo->todoID] = $todo;
        }
    }

    /**
     * @inheritDoc
     */
    public function getSubjectFieldName(): string
    {
        return $this->getTableName() . '.todoName';
    }

    /**
     * @inheritDoc
     */
    public function getObject(int $objectID): ?ISearchResultObject
    {
        return $this->messageCache[$objectID] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getTableName(): string
    {
        return 'todolist' . WCF_N . '_todo';
    }

    /**
     * @inheritDoc
     */
    public function getIDFieldName(): string
    {
        return $this->getTableName() . '.todoID';
    }

    /**
     * @inheritDoc
     */
    public function getConditionBuilder(array $parameters): ?PreparedStatementConditionBuilder
    {
        $this->readParameters($parameters);
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $this->initCategoryCondition($conditionBuilder);

        return $conditionBuilder;
    }

    private function initCategoryCondition(PreparedStatementConditionBuilder $conditionBuilder): void
    {
        $selectedCategoryIDs = $this->getTodoCategoryIDs($this->todoCategoryID);
        $accessibleCategoryIDs = TodoCategory::getAccessibleCategoryIDs();
        if (!empty($selectedCategoryIDs)) {
            $selectedCategoryIDs = \array_intersect($selectedCategoryIDs, $accessibleCategoryIDs);
        } else {
            $selectedCategoryIDs = $accessibleCategoryIDs;
        }

        if (empty($selectedCategoryIDs)) {
            $conditionBuilder->add('1=0');
        } else {
            $conditionBuilder->add($this->getTableName() . '.categoryID IN (?)', [$selectedCategoryIDs]);
        }
    }

    private function getTodoCategoryIDs(int $categoryID): array
    {
        $categoryIDs = [];
        if ($categoryID) {
            if (($category = TodoCategory::getCategory($categoryID)) !== null) {
                $categoryIDs[] = $categoryID;
                foreach ($category->getAllChildCategories() as $childCategory) {
                    $categoryIDs[] = $childCategory->categoryID;
                }
            }
        }

        return $categoryIDs;
    }

    /**
     * @inheritDoc
     */
    public function getFormTemplateName(): string
    {
        return 'searchTodo';
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalData(): ?array
    {
        return ['todoCategoryID' => $this->todoCategoryID];
    }

    /**
     * @inheritDoc
     */
    public function assignVariables(): void
    {
        WCF::getTPL()->assign([
            'todoCategoryList' => (new TodoCategoryNodeTree('de.julian-pfeil.todolist.todo.category'))->getIterator(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function isAccessible(): bool
    {
        if ($this->todoCategoryID == 0) {
            if (!empty($parameters['todoCategoryID'])) {
                $this->todoCategoryID = \intval($parameters['todoCategoryID']);
            }
        }

        if ($this->todoCategoryID != 0) {
            $category = TodoCategory::getCategory($this->todoCategoryID);

            return $category->canView();
        } else {
            $categoryNodeTree = new TodoCategoryNodeTree(TodoCategory::OBJECT_TYPE_NAME, 0, false);
            $categoryNodeTree->loadCategoryLists();

            return $categoryNodeTree->canAddTodoInAnyCategory();
        }
    }

    private function readParameters(array $parameters): void
    {
        if (!empty($parameters['todoCategoryID'])) {
            $this->todoCategoryID = \intval($parameters['todoCategoryID']);
        }
    }
}
