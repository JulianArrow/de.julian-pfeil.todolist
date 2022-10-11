{capture assign='pageTitle'}{$todo} - {lang}todolist.general.list{/lang}{/capture}

{capture assign='contentTitle'}{lang}todolist.general.todo{/lang}{/capture}

{capture assign='contentHeader'}
    {include file='todoHeader' application='todolist'}
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

{if "TODOLIST_MODIFICATION_LOG_PLUGIN"|defined}
    <script data-relocate="true">
    $(function() {
        document.getElementById("todoLogLink").addEventListener("click", function () {
            if (this.href) {
                window.location.href = this.href;
            }
        });
    });
    </script>
{/if}

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
