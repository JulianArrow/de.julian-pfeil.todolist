<?php

namespace todolist\form;

use todolist\data\todo\Todo;
use todolist\system\user\notification\object\TodoUserNotificationObject;
use todolist\data\category\TodoCategory;
use wcf\system\exception\IllegalLinkException;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\system\request\LinkHandler;

/**
 * Shows the form to edit an existing todo.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package WoltLabSuite\Core\Acp\Form
 */
class TodoEditForm extends TodoAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'de.julian-pfeil.todolist.TodoList';

    /**
     * @inheritDoc
     */
    public $formAction = 'update';

    /**
      * todo id
      *
      * @var int;
      */
     public $todoID;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if ($this->formAction == 'update') {
            #todoID
            if (isset($_REQUEST['id'])) {
                $this->formObject = new Todo($_REQUEST['id']);

                if (!$this->formObject->getObjectID()) {
                    throw new IllegalLinkException();
                }

                $this->categoryID = $this->formObject->categoryID;
                $this->category = TodoCategory::getCategory($this->categoryID);

                if ($this->category === null) {
                    throw new IllegalLinkException();
                }
            } else {
                throw new IllegalLinkException();
            }
        }
    }

    /**
    * @inheritDoc
    */
    public function checkPermissions()
    {
        parent::checkPermissions();

        if ($this->formAction == 'update') {
            if (!$this->formObject->canEdit()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        if ($this->formAction == 'update' &&  WCF::getUser()->userID != $this->formObject->userID) {
            $recipientIDs = [$this->formObject->userID];
            UserNotificationHandler::getInstance()->fireEvent(
                'todo', // event name
                'de.julian-pfeil.todolist.todo', // event object type name
                new TodoUserNotificationObject(new Todo($this->formObject->todoID)),
                $recipientIDs
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function saved()
    {
        parent::saved();

        HeaderUtil::redirect(
            LinkHandler::getInstance()->getLink('Todo', [
                'application' => 'todolist',
                'object' => $this->formObject
            ])
        );
        exit;
    }
}
