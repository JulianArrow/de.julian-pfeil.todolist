<?php

namespace todolist\form;

use todolist\data\todo\category\TodoCategory;
use todolist\data\todo\category\TodoCategoryNodeTree;
use todolist\data\todo\TodoAction;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\wysiwyg\WysiwygFormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the form to create a new todo.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage form
 */
class TodoAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'de.julian-pfeil.todolist.TodoList';

    /**
     * @inheritDoc
     */
    public $formAction = 'create';

    /**
     * @inheritDoc
     */
    public $neededPermissions = [];

    /**
     * @inheritDoc
     */
    public $objectActionClass = TodoAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = TodoEditForm::class;

    /**
     * category object
     *
     * @var        Category
     */
    public $category;

    /**
     * category id
     */
    public $categoryID = 0;

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        /* breadcrumbs */
        if ($this->formAction == 'create') {
            if ($this->categoryID != 0) {
                PageLocationManager::getInstance()->addParentLocation('de.julian-pfeil.todolist.TodoList', $this->categoryID, $this->category);
            }
            PageLocationManager::getInstance()->addParentLocation('de.julian-pfeil.todolist.TodoList');
        }
    }

    /**
    * @inheritDoc
    */
    public function readParameters()
    {
        parent::readParameters();

        //categoryID
        if ($this->formAction == 'create') {
            if (isset($_REQUEST['id'])) {
                $this->categoryID = \intval($_REQUEST['id']);
                $this->category = TodoCategory::getCategory($this->categoryID);

                if ($this->category === null) {
                    throw new IllegalLinkException();
                }
            }
        }
    }

    /**
    * @inheritDoc
    */
    public function checkPermissions()
    {
        if ($this->formAction == 'create') {
            if ($this->categoryID) {
                if (!$this->category->canAddTodo()) {
                    $categoryNodeTree = new TodoCategoryNodeTree(TodoCategory::OBJECT_TYPE_NAME, 0, false);
                    $categoryNodeTree->loadCategoryLists();

                    if (!$categoryNodeTree->canAddTodoInAnyCategory()) {
                        throw new PermissionDeniedException();
                    } else {
                        $this->category = null;
                        $this->categoryID = 0;
                    }
                }
            } else {
                $categoryNodeTree = new TodoCategoryNodeTree(TodoCategory::OBJECT_TYPE_NAME, 0, false);
                $categoryNodeTree->loadCategoryLists();

                if (!$categoryNodeTree->canAddTodoInAnyCategory()) {
                    throw new PermissionDeniedException();
                }
            }
        }

        $this->buildForm();
    }

    /**
     * @inheritDoc
     */
    protected function createForm()
    {
        parent::createForm();

        /* dataContainer */
        $dataContainer = FormContainer::create('data')
            ->label('wcf.global.form.data');

        /* wysiwygContainer */
        $wysiwygContainer = WysiwygFormContainer::create('description')
            ->label('todolist.column.description')
            ->messageObjectType('de.julian-pfeil.todolist.todo.content')
            ->supportMentions(true)
            ->addSettingsNode(
                /* isDone settingsNode */
                BooleanFormField::create('isDone')
                    ->label('todolist.column.isDone')
                    ->value(false)
            );

        /* append to dataContainer */
        $dataContainer->appendChild(
            TextFormField::create('todoName')
                ->label('todolist.column.todoName')
                ->required()
                ->autoFocus()
                ->maximumLength(255)
        );

        /* infoContainer */
        $infoContainer = FormContainer::create('info')
            ->label('todolist.general.info');

        /* category selection */
        $categoryField = SingleSelectionFormField::create('categoryID')
            ->label('todolist.category.title')
            ->filterable()
            ->required()
            ->options(static function () {
                $categoryTree = new TodoCategoryNodeTree('de.julian-pfeil.todolist.todo.category');
                $categoryList = $categoryTree->getIterator();
                $nestedOptions = [];

                foreach ($categoryList as $categoryNode) {
                    $category = $categoryNode->getDecoratedObject();

                    if ($category->canView()) {
                        $nestedOptions[] = [
                            'label' => $categoryNode->getTitle(),
                            'value' => $categoryNode->categoryID,
                            'depth' => $categoryNode->getDepth() - 1,
                            'isSelectable' => $category->canAddTodo(),
                        ];
                    }
                }

                return $nestedOptions;
            }, true);

        // set category when preselect
        if (
            isset($this->categoryID)
            && \in_array($this->categoryID, \array_keys($categoryField->getOptions()))
        ) {
            $categoryField->value($this->categoryID);
        }

        //set category when only one selectable
        if (\count($categoryField->getOptions()) == 1) {
            $categoryField->value(\array_keys($categoryField->getOptions())[0]);
        }

        /* append data, info & categorySelection to form */
        $this->form->appendChildren([
            $dataContainer,
            $infoContainer,
            FormContainer::create('categorySelection')
                ->appendChildren([$categoryField]),
        ]);

        /* append wysiwygContainer to form*/
        $this->form->appendChild($wysiwygContainer);
    }

    /**
     * @inheritDoc
     */
    protected function setFormAction()
    {
        $parameters = [];
        $parameters['categoryID'] = $this->categoryID;
        if ($this->formObject !== null) {
            if ($this->formObject instanceof IRouteController) {
                $parameters['object'] = $this->formObject;
                $this->todoID = $this->formObject->todoID;
            } else {
                $object = $this->formObject;
                $this->todoID = $object->todoID;
                $parameters['id'] = $object->todoID;
            }
        }

        $this->form->action(LinkHandler::getInstance()->getControllerLink(static::class, $parameters));
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        if ($this->formAction == 'create') {
            WCF::getTPL()->assign([
                'success' => true,
                'objectEditLink' => LinkHandler::getInstance()->getControllerLink(TodoEditForm::class, ['id' => $this->objectAction->getReturnValues()['returnValues']->todoID]),
            ]);
        }
    }
}
