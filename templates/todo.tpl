{capture assign='pageTitle'}{$todo} - {lang}todolist.general.list{/lang}{/capture}

{capture assign='contentTitle'}{lang}todolist.general.todo{/lang}{/capture}

{capture assign='contentHeader'}
    {include file='todoHeader' application='todolist'}
{/capture}

{capture assign='contentInteractionButtons'}
    {hascontent}
        <ul class="buttonList">
            {content}
                
                {if $__wcf->user->userID && $__wcf->user->userID != $todo->userID}
                    <a href="#" class="contentInteractionButton jsSubscribeButton jsOnly button small{if $todo->isSubscribed()} active{/if}" data-object-type="de.julian-pfeil.todolist.todo" data-object-id="{@$todo->todoID}"><span class="icon icon16 fa-bookmark{if !$todo->isSubscribed()}-o{/if}"></span> <span>{lang}wcf.user.objectWatch.button.subscribe{/lang}</span></a>
                
                    <script data-relocate="true">
                    $(function() {
                        WCF.Language.addObject({
                            'wcf.user.objectWatch.manageSubscription': '{jslang}wcf.user.objectWatch.manageSubscription{/jslang}'
                        });
                        
                        new WCF.User.ObjectWatch.Subscribe();
                    });
                    </script>
                {/if}

                {if $todo->canEdit()}
                    <li class="jsTodoInlineEditorContainer" data-todo-id="{@$todo->todoID}">
                        <a href="{link application='todolist' controller='TodoEdit' id=$todo->todoID}{/link}" class="contentInteractionButton small button jsTodoInlineEditor" id="todoEditButton">
                            <span class="icon icon16 fa-pencil"></span>
                            <span>{lang}wcf.global.button.edit{/lang}</span>
                        </a>
                    </li>
                {/if}
            
                {include file='todoAddButton' application='todolist' listItem=true classes='small contentInteractionButton'}
            
                {event name='afterContentInteractionButtons'}
            {/content}
        </ul>
    {/hascontent}
{/capture}

{include file='header'}

{include file='todoStructure' application='todolist' pageFrom='todo'}

{event name='beforeContentFooter'}

<footer class="contentFooter">
    {hascontent}
        <nav class="contentFooterNavigation">
            <ul>
                {content}{event name='contentFooterNavigation'}{/content}
            </ul>
        </nav>
    {/hascontent}
</footer>

<script data-relocate="true">
    $(function() {
        WCF.Language.addObject({	
            'todolist.action.markAsDone':					'{jslang}todolist.action.markAsDone{/jslang}',
            'todolist.action.markAsUndone':					'{jslang}todolist.action.markAsUndone{/jslang}',
            'todolist.action.confirmDelete':				'{jslang}todolist.action.confirmDelete{/jslang}',
            'todolist.general.isDone':						'{jslang}todolist.general.isDone{/jslang}',
            'todolist.general.isUndone':						'{jslang}todolist.general.isUndone{/jslang}'
        });
        
        var $updateHandler = new Todolist.Todo.UpdateHandler.Todo();
        
        new Todolist.Todo.MarkAsDone($updateHandler);
        
        var $inlineEditor = new Todolist.Todo.InlineEditor('.jsTodoInlineEditorContainer');
        $inlineEditor.setRedirectURL('{link application='todolist' controller='TodoList' encode=false}{/link}');
        $inlineEditor.setUpdateHandler($updateHandler);
        $inlineEditor.setPermissions({
            canDeleteTodo:		{if $todo->canDelete()}1{else}0{/if},
            canMarkAsDone:		{if $todo->canEdit()}1{else}0{/if}
        });
    });
</script>

{if MODULE_LIKE && $__wcf->getUser()->userID && $__wcf->getSession()->getPermission('user.like.canViewLike')}
    <script data-relocate="true">
        require(['WoltLabSuite/Core/Ui/Reaction/Handler'], function(UiReactionHandler) {
            new UiReactionHandler('de.julian-pfeil.todolist.likeableTodo', {
                // settings
                isSingleItem: true,
                
                // selectors
                buttonSelector: '#todoReactButton',
                containerSelector: '#generalTab',
                summaryListSelector: '.reactionSummaryList'
            });
        });
    </script>
{/if}

{event name='additionalJavascript'}

{include file='footer'}
