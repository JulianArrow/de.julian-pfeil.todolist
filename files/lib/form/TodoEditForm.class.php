<?php

namespace todolist\form;

use todolist\data\todo\Todo;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

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
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->formObject = new Todo($_REQUEST['id']);

            if (!$this->formObject->getObjectID()) {
                throw new IllegalLinkException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        if ($this->formAction == 'update' &&  WCF::getUser()->userID != $this->formObject->userID)
        {

            $recipientIDs = [$this->formObject->userID];
            UserNotificationHandler::getInstance()->fireEvent(
                'todo', // event name
                'de.julian-pfeil.todolist.todo', // event object type name
                new TodoUserNotificationObject(new Todo($this->formObject->todoID)),
                $recipientIDs
            );
        }
    }
}
