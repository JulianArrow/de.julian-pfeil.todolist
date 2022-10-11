<?php

namespace todolist\form;

use todolist\data\todo\category\TodoCategory;
use todolist\data\todo\category\TodoCategoryNodeTree;
use todolist\data\todo\TodoAction;
use todolist\system\cache\builder\TodoCategoryLabelCacheBuilder;
use todolist\system\form\builder\container\wysiwyg\TodoWysiwygFormContainer;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\label\LabelFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\tag\TagFormField;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;
use wcf\system\request\LinkHandler;

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
    public $activeMenuItem = 'de.julian-pfeil.todolist.AddTodo';

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
    public $category = null;

    /**
     * category id
     */
    public $categoryID = 0;

    /**
    * @inheritDoc
    */
    public function readParameters()
    {
        parent::readParameters();

        #categoryID
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
        $wysiwygContainer = TodoWysiwygFormContainer::create('description')
        ->label('todolist.column.description')
        ->required()
        ->messageObjectType('de.julian-pfeil.todolist.todo.content')
        ->supportMentions(true)
        ->addSettingsNode(
            /* isDone settingsNode */
            BooleanFormField::create('isDone')
                ->label('todolist.column.isDone')
                ->value(false)
        );

        /* enableComments settingsNode */
        if (defined('TODOLIST_COMMENTS_PLUGIN')) {
            $wysiwygContainer->addSettingsNode(
                BooleanFormField::create('enableComments')
                    ->label('todolist.comment.enable')
                    ->description('todolist.comment.enable.description')
                    ->value(true),
            );
        }

        /* editReason settingsNode */
        if (defined('TODOLIST_MODIFICATION_LOG_PLUGIN') && $this->formAction == 'update') {
            $wysiwygContainer->addSettingsNode(
                TextFormField::create('editReason')
                    ->label('todolist.column.editReason')
                    ->maximumLength(255),
            );
        }

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
            ->label('todo.general.info');


        if (defined('TODOLIST_TAGGING_PLUGIN')) {
            /* tags */
            $infoContainer->appendChild(
                TagFormField::create('tags')
                    ->objectType('de.julian-pfeil.todolist.todo')
                    ->available(MODULE_TAGGING)
            );
        }

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

        if (defined('TODOLIST_LABELS_PLUGIN')) {
            /* labels */
            $assignableLabelGroups = TodoCategory::getAccessibleLabelGroups();
            if (\count($assignableLabelGroups)) {
                $infoContainer->appendChildren(
                    LabelFormField::createFields('de.julian-pfeil.todolist.todo', $assignableLabelGroups, 'labels')
                );


                $labelGroupsToCategories = [];
                foreach (TodoCategoryLabelCacheBuilder::getInstance()->getData() as $categoryID => $labelGroupIDs) {
                    foreach ($labelGroupIDs as $labelGroupID) {
                        if (!isset($labelGroupsToCategories[$labelGroupID])) {
                            $labelGroupsToCategories[$labelGroupID] = [];
                        }
                        $labelGroupsToCategories[$labelGroupID][] = $categoryID;
                    }
                }

                foreach ($assignableLabelGroups as $labelGroup) {
                    if (isset($labelGroupsToCategories[$labelGroup->groupID])) {
                        $labelField = $infoContainer->getNodeById('labels' . $labelGroup->groupID);
                        $labelField->addDependency(
                            ValueFormFieldDependency::create('labels' . $labelGroup->groupID)
                                ->fieldId('categoryID')
                                ->values($labelGroupsToCategories[$labelGroup->groupID])
                        );
                    }
                }
            }
        }



        /* append data, info & categorySelection to form */
        $this->form->appendChildren([
            $dataContainer,
            $infoContainer,
            FormContainer::create('categorySelection')
                ->appendChildren([$categoryField])
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
                'objectEditLink' => LinkHandler::getInstance()->getControllerLink(TodoEditForm::class, ['id' => $this->objectAction->getReturnValues()['returnValues']->todoID])
            ]);
        }
    }
}
