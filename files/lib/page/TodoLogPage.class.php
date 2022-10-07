<?php
namespace todolist\page;
use todolist\data\todo\Todo;
use todolist\data\modification\log\TodoLogModificationLogList;
use todolist\system\TODOLISTCore;
use wcf\page\SortablePage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Todolists the todo log page.
 * 
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class TodoLogPage extends SortablePage {
    /**
     * @inheritDoc
     */
    public $defaultSortField = 'time';
    public $defaultSortOrder = 'DESC';
    public $validSortFields = ['logID', 'time', 'username'];
    
    /**
     * todo data
     */
    public $todoID = 0;
    public $todo;
    
    /**
     * @inheritDoc
     */
    public $objectListClassName = TodoLogModificationLogList::class;
    
    /**
     * @inheritDoc
     */
    public $neededPermissions = [];
    
    /**
     * @inheritDoc
     */
    public $neededModules = ['TODOLIST_MODIFICATION_LOG_PLUGIN'];
    
    /**
     * @inheritDoc
     */
    public function assignVariables() {
        parent::assignVariables();
        
        WCF::getTPL()->assign([
                'todo' => $this->todo
        ]);
    }
    
    /**
     * @inheritDoc
     */
    public function checkPermissions() {
        if (!$this->todo->canEdit()) {
            throw new PermissionDeniedException();
        }
    }
    
    /**
     * @inheritDoc
     */
    public function readParameters() {
        parent::readParameters();

        #todoID
        if (isset($_REQUEST['id'])) {
            $this->todoID = \intval($_REQUEST['id']);
        }
        $this->todo = new Todo($this->todoID);
        if (!$this->todo->todoID) {
            throw new IllegalLinkException();
        }
    }
    
    /**
     * @inheritDoc
     */
    protected function initObjectList() {
        parent::initObjectList();
        
        $this->objectList->setTodo($this->todo->getDecoratedObject());
    }
}
