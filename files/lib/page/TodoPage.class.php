<?php

namespace todolist\page;

use wcf\page\AbstractPage;
use todolist\data\todo\Todo;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the details of a certain todo.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package WoltLabSuite\Core\Page
 */
class TodoPage extends AbstractPage
{


    /**
     * shown todo
     * @var Todo
     */
    public $todo;

    /**
     * id of the shown todo
     * @var int
     */
    public $todoID = 0;

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.todolist.canSeeTodos'];

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'todo' => $this->todo,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->todoID = \intval($_REQUEST['id']);
        }
        $this->todo = new Todo($this->todoID);
        if (!$this->todo->todoID) {
            throw new IllegalLinkException();
        }
    }
}
