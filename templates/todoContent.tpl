
    
{* Tab 1 *}
<div id="generalTab" class="todoContent section{if $tabMenu == 1} tabMenuContent{/if}" 
    {if MODULE_LIKE}
        {@$__wcf->getReactionHandler()->getDataAttributes('de.julian-pfeil.todolist.likeableTodo', $todo->todoID)}
    {/if}
    {event name='todoContentAttributes'}
>
    <div class="section">
        <div class="section">
            {if $todo->getFormattedMessage() == ''}
                <p class="info">{lang}todolist.general.noDescription{/lang}</p>
            {else}
                <div class="todoDescription htmlContent userMessage">
                    {@$todo->getFormattedMessage()}
                </div>
            {/if}
        </div>
        
        {event name='afterTodoDescription'}

        {include file='todoReactionSummary' application='todolist'}

        {event name='beforeTodoButtons'}

        {hascontent}
            <div class="section">
                <ul class="todoButtons buttonList" data-todo-id="{@$todo->todoID}">
                    {content}
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

{include file='__todoComments' application='todolist'}

{event name='tabMenuContents'}