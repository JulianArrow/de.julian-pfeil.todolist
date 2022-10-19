
    
{* Tab 1 *}
<div id="generalTab" class="todoContent section{if $tabMenu == 1} tabMenuContent{/if}" 
    {if MODULE_LIKE}
        {@$__wcf->getReactionHandler()->getDataAttributes('de.julian-pfeil.todolist.likeableTodo', $todo->todoID)}
    {/if}
    {event name='todoContentAttributes'}
>
    <div class="section">
        <div class="section">
            <p class="todoDescription htmlContent userMessage">
                {@$todo->getFormattedMessage()}
            </p>
        </div>
        
        {event name='afterTodoDescription'}

        {include file='todoReactionSummary' application='todolist'}

        {event name='beforeTodoButtons'}

        {hascontent}
            <div class="section">
                <ul class="todoButtons buttonList smallButtons jsTodoInlineEditorContainer" data-todo-id="{@$todo->todoID}">
                    {content}
                        {if $todo->canEdit()}
                            <li>
                                <a href="{link application='todolist' controller='TodoEdit' id=$todo->todoID}{/link}" class="small button jsTodoInlineEditor" id="todoEditButton">
                                    <span class="icon icon16 fa-pencil"></span>
                                    <span>{lang}wcf.global.button.edit{/lang}</span>
                                </a>
                            </li>
                        {/if}
                        
                        {include file='todoAddButton' application='todolist'}

                        {if MODULE_LIKE}
                            {include file='todoReactButton' application='todolist'}
                        {/if}

                        {event name='todoButtons'}
                    {/content}
                </ul>
            </div>	
        {/hascontent}
    </div>
</div>
{* End - Tab 1 *}

{event name='tabMenuContents'}