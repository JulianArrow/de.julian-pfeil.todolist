/**
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package Todolist/Core
 */

/**
 * Initialize Todolist namespaces
 */
 var Todolist = { };
 Todolist.Todo = { };

/**
 * Inline editor for todos.
 */
 Todolist.Todo.InlineEditor = WCF.InlineEditor.extend({
    /**
     * current editor environment
     */
    _environment: 'todo',
    
    /**
     * list of permissions
     */
    _permissions: {},
    
    /**
     * redirect URL
     */
    _redirectURL: '',
    
    /**
     * todo update handler
     */
    _updateHandler: null,
    
    /**
     * @see WCF.InlineEditor._setOptions()
     */
    _setOptions: function () {
        this._environment = 'todo';
        
        this._options = [
            // edit
            {
                label: WCF.Language.get('wcf.global.button.edit'),
                optionName: 'edit',
                isQuickOption: true
            },

            // delete
            {
                label: WCF.Language.get('wcf.global.button.delete'), 
                optionName: 'delete'
            },
            
            // divider
            {optionName: 'divider'},
            
            // markAsDone
            {
                label: WCF.Language.get('todolist.action.markAsDone'),
                optionName: 'markAsDone'
            },
            {
                label: WCF.Language.get('todolist.action.markAsUndone'),
                optionName: 'markAsUndone'
            }
        ];
    },
    
    /**
     * Returns current update handler.
     */
    setUpdateHandler: function (updateHandler) {
        this._updateHandler = updateHandler;
    },
    
    /**
     * @see WCF.InlineEditor._getTriggerElement()
     */
    _getTriggerElement: function (element) {
        return element.find('.jsTodoInlineEditor');
    },
    
    /**
     * @see WCF.InlineEditor._show()
     */
    _show: function (event) {
        var $elementID = $(event.currentTarget).data('elementID');
        
        // build dropdown
        var $trigger = null;
        if (!this._dropdowns[$elementID]) {
            $trigger = this._getTriggerElement(this._elements[$elementID]).addClass('dropdownToggle');
            $trigger.parent().addClass('dropdown');
            this._dropdowns[$elementID] = $('<ul class="dropdownMenu" />').insertAfter($trigger);
        }
        
        this._super(event);
        
        if ($trigger !== null) {
            WCF.Dropdown.initDropdown($trigger, true);
        }
        
        return false;
    },
    
    /**
     * @see WCF.InlineEditor._validate()
     */
    _validate: function (elementID, optionName) {
        var $todoID = $('#' + elementID).data('todoID');
        
        switch (optionName) {
            // delete
            case 'delete':
                if (!this._getPermission('canDeleteTodo')) {
                    return false;
                }
                
                return true;
                break;
            // markAsDone
            case 'markAsDone':
                if (!this._getPermission('canMarkAsDone')) {
                    return false;
                }
                
                return !(this._updateHandler.getValue($todoID, 'isDone'));
                break;
            case 'markAsUndone':
                if (!this._getPermission('canMarkAsDone')) {
                    return false;
                }
                
                return (this._updateHandler.getValue($todoID, 'isDone'));
                break;
                
            // edit
            case 'edit':
                return true;
                break;
        }
        
        return false;
    },
    
    /**
     * @see WCF.InlineEditor._execute()
     */
    _execute: function (elementID, optionName) {
        // abort if option is invalid or not accessible
        if (!this._validate(elementID, optionName)) {
            return false;
        }
        
        switch (optionName) {
            case 'delete':
                var self = this;
                WCF.System.Confirmation.show(WCF.Language.get('todolist.action.confirmDelete'), function (action) {
                    if (action === 'confirm') {
                        self._updateTodo(elementID, optionName, {deleted: 1});
                    }
                });
                break;
            case 'markAsDone':
            case 'markAsUndone':
                this._updateTodo(elementID, optionName, {isDone: (optionName === 'markAsDone' ? 1 : 0)});
                break;
                
            case 'edit':
                window.location = this._getTriggerElement($('#' + elementID)).prop('href');
                break;
                
            default:
                return false;
                break;
        }
        
        return true;
    },
    
    /**
     * Updates todo properties.
     */
    _updateTodo: function (elementID, optionName, data) {
        if (optionName === 'delete') {
            var self = this;
            var $todoID = this._elements[elementID].data('todoID');
            
            new WCF.Action.Proxy({
                autoSend: true,
                data: {
                    actionName:	optionName,
                    className: 	'todolist\\data\\todo\\TodoAction',
                    objectIDs:	[$todoID]
                },
                success: function (data) {
                    self._updateHandler.update($todoID, data.returnValues.todoData[$todoID]);
                }
            });
        }
        else {
            this._updateData.push({
                data: 		data,
                elementID:	elementID,
                optionName:	optionName
            });
            
            this._proxy.setOption('data', {
                actionName:	optionName,
                className:	'todolist\\data\\todo\\TodoAction',
                objectIDs:	[this._elements[elementID].data('todoID')],
                parameters:	{
                    data: data
                }
            });
            this._proxy.sendRequest();
        }
    },
    
    /**
     * @see WCF.InlineEditor._updateState()
     */
    _updateState: function(requestData) {
        
        // user feedback
        this._notification.show();
        
        // update
        for (var $i = 0, $length = this._updateData.length; $i < $length; $i++) {
            var data = this._updateData[$i];
            var todoID = $('#' + data.elementID).data('todoID');
            var updateData = data.data;
            
            this._updateHandler.update(todoID, updateData);
        }
    },
    
    /**
     * Returns a specific permission.
     */
    _getPermission: function (permission) {
        if (this._permissions[permission]) {
            return this._permissions[permission];
        }
        
        return 0;
    },
    
    /**
     * Sets current redirect URL.
     */
    setRedirectURL: function (redirectURL) {
        this._redirectURL = redirectURL;
    },
    
    /**
     * Sets a permission.
     */
    setPermission: function (permission, value) {
        this._permissions[permission] = value;
    },
    
    /**
     * Sets permissions.
     */
    setPermissions: function (permissions) {
        for (var $permission in permissions) {
            this.setPermission($permission, permissions[$permission]);
        }
    }
});

Todolist.Todo.UpdateHandler = Class.extend({
    /**
     * todo list
     */
    _todos: {},
    
    /**
     * mark as done handler
     */
     _markAsDoneHandler: null,

    /**
     * Initializes the todo update handler.
     */
    init: function () {
        var self = this;
        $('.todoHeader').each(function (index, todo) {
            var $todo = $(todo);
            
            self._todos[$todo.data('objectID')] = $todo;
        });
    },

    /**
     * Sets mark as done handler.
     */
    setMarkAsDoneHandler: function (markAsDoneHandler) {
        this._markAsDoneHandler = markAsDoneHandler;
    },
    
    /**
     * Updates a set of properties for given todo id.
     */
    update: function (todoID, data) {
        if (!this._todos[todoID]) {
            console.debug("[Todolist.Todo.UpdateHandler] Unknown todo id " + todoID);
            return;
        }
        
        for (var $property in data) {
            this._updateProperty(todoID, $property, data[$property]);
        }
    },
    
    /**
     * Wrapper for property updating.
     */
    _updateProperty: function (todoID, property, value) {
        switch (property) {
            case 'deleted':
                this._delete(todoID, value);
                break;
                
            case 'isDone':
                if (value) {
                    this._markAsDone(todoID);
                }
                else {
                    this._markAsUndone(todoID);
                }
                break;
                
            default:
                this._handleCustomProperty(todoID, property, value);
                break;
        }
    },
    
    /**
     * Handles custom properties not known
     */
    _handleCustomProperty: function (todoID, property, value) {
        this._todos[todoID].trigger('todoUpdateHandlerProperty', [todoID, property, value]);
    },
    
    /**
     * Deletes an todo.
     */
    _delete: function (todoID, link) {
    },
    /**
     * Sets an todo as featured.
     */
    _markAsDone: function (todoID) {
        this._todos[todoID].data('isDone', 1);
    },
    
    /**
     * Unsets as todo as featured.
     */
    _markAsUndone: function (todoID) {
        this._todos[todoID].data('isDone', 0);
    },
    
    /**
     * Returns generic property values for an todo.
     */
    getValue: function (todoID, property) {
        if (!this._todos[todoID]) {
            console.debug("[Todolist.Todo.UpdateHandler] Unknown todo id " + todoID);
            return;
        }
        
        switch (property) {
            case 'isDone':
                return this._todos[todoID].data('isDone');
                break;
        }
    }
});

/**
 * Todo update handler for todo page.
 */
 Todolist.Todo.UpdateHandler.Todo = Todolist.Todo.UpdateHandler.extend({
    /**
     * @see Todolist.Todo.UpdateHandler._delete()
     */
    _delete: function (todoID, link) {
        new WCF.PeriodicalExecuter(function (pe) {
            pe.stop();
            
            window.location = link;
        }, 1000);
    },
    
    /**
     * @see Todolist.Todo.UpdateHandler._markAsUndone()
     */
    _markAsUndone: function (todoID) {
        this._super(todoID);

        $('.jsMarkAsDone .doneTitle').text(WCF.Language.get('todolist.general.undone'));
        $('.jsMarkAsDone .icon')
            .removeClass('fa-check-square-o')
            .addClass('fa-square-o')
            .attr('aria-label', WCF.Language.get('todolist.general.undone'))
            .attr('data-tooltip', WCF.Language.get('todolist.general.undone'));
    },
    
    /**
     * @see Todolist.Todo.UpdateHandler._markAsDone()
     */
    _markAsDone: function (todoID) {
        this._super(todoID);

        $('.jsMarkAsDone .doneTitle').text(WCF.Language.get('todolist.general.done'));
        $('.jsMarkAsDone .icon')
            .addClass('fa-check-square-o')
            .removeClass('fa-square-o')
            .attr('aria-label', WCF.Language.get('todolist.general.done'))
            .attr('data-tooltip', WCF.Language.get('todolist.general.done'));
    },
});

/**
 * Todo update handler for todolist page.
 */
 Todolist.Todo.UpdateHandler.Todolist = Todolist.Todo.UpdateHandler.extend({
    
    /**
     * @see Todolist.Todo.UpdateHandler._markAsUndone()
     */
    _markAsUndone: function (todoID) {
        this._super(todoID);

        $('[data-todo-id=' + todoID + '] .jsMarkAsDone.icon')
            .removeClass('fa-check-square-o')
            .addClass('fa-square-o')
            .attr('aria-label', WCF.Language.get('todolist.general.undone'))
            .attr('data-tooltip', WCF.Language.get('todolist.general.undone'));
    },
    
    /**
     * @see Todolist.Todo.UpdateHandler._markAsDone()
     */
    _markAsDone: function (todoID) {
        this._super(todoID);

        $('[data-todo-id=' + todoID + '] .jsMarkAsDone.icon')
            .addClass('fa-check-square-o')
            .removeClass('fa-square-o')
            .attr('aria-label', WCF.Language.get('todolist.general.done'))
            .attr('data-tooltip', WCF.Language.get('todolist.general.done'));
    },
});


/**
 * Provides a toggle handler for mark as done.
 */
 Todolist.Todo.MarkAsDone = Class.extend({
    /**
     * action proxy
     * @var        WCF.Action.Proxy
     */
    _proxy: null,
    /**
     * todo update handler
     */
    _updateHandler: null,
    /**
     * list of todos
     */
    _todos: {},
    /**
     * Initializes the mark as done handler.
     */
    init: function (updateHandler) {
        this._updateHandler = updateHandler;
        this._todos = {};
        this._proxy = new WCF.Action.Proxy({
            success: $.proxy(this._success, this)
        });
        var self = this;
        $('.todoHeader').each(function (index, todo) {
            var $todo = $(todo);
            var $todoID = $todo.data('todoID');
            self._todos[$todoID] = $todo;
            if ($todo.data('canMarkAsDone')) {
                self.watch($todoID);
            }
        });
        // register with update handler
        this._updateHandler.setMarkAsDoneHandler(this);
    },
    /**
     * Watches for clicks on "mark as done" icon.
     */
    watch: function (todoID) {
        if (this._todos[todoID] && this._todos[todoID].data('canMarkAsDone')) {
            this._todos[todoID].find('.jsMarkAsDone').data('todoID', todoID).css('cursor', 'pointer').click($.proxy(this._click, this));
        }
    },
    /**
     * Handles click on the "mark as done" icon.
     */
    _click: function (event) {
        var $icon = $(event.currentTarget);
        if ($icon[0].nodeName == 'LI')
            var $isDone = ($icon.find('.fa-check-square-o').length ? true : false);
        else
            var $isDone = ($icon.hasClass('fa-check-square-o') ? true : false);
        this._proxy.setOption('data', {
            actionName: 'markAs' + ($isDone ? 'Undone' : 'Done'),
            className: 'todolist\\data\\todo\\TodoAction',
            objectIDs: [$icon.data('todoID')]
        });
        this._proxy.sendRequest();
        if ($isDone)
            this._updateHandler._markAsUndone($icon.data('todoID'));
        else
            this._updateHandler._markAsDone($icon.data('todoID'));
    },
    /**
     * Handles successful AJAX requests.
     */
    _success: function (data, textStatus, jqXHR) {
        for (var $todoID in data.returnValues.todoData) {
            this._updateHandler.update($todoID, data.returnValues.todoData[$todoID]);
        }
    }
});
