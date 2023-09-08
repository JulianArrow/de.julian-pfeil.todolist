{capture assign='pageTitle'}{$todo} - {lang}todolist.general.list{/lang}{/capture}

{capture assign='contentTitle'}{lang}todolist.general.todo{/lang}{/capture}

{capture assign='contentHeaderNavigation'}
    {include file='todoAddButton' application='todolist' listItem=true}
{/capture}

{capture assign='contentHeader'}
    {include file='todoHeader' application='todolist'}
{/capture}

{capture assign='contentInteractionButtons'}
    {hascontent}
        <ul class="buttonList">
            {content}

                {if $__wcf->user->userID != $todo->userID}
                    {include file='__userObjectWatchButton' isSubscribed=$todo->isSubscribed() objectType='de.julian-pfeil.todolist.todo' objectID=$todo->todoID}
                {/if}
                {if $todo->canEdit()}
                    <li>
                        <a href="{link application='todolist' controller='TodoEdit' object=$todo}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip button">
                            {icon name='pencil'}
                            <span class="invisible">{lang}wcf.global.button.edit{/lang}</span>
                        </a>
                    </li>
                {/if}
            
                {event name='afterContentInteractionButtons'}
            {/content}
        </ul>
    {/hascontent}
{/capture}

{hascontent}
    {capture assign='contentInteractionDropdownItems'}
            {content}
                {if $todo->canEdit()}
                    <li>
                        <a 
                            href="#"
                            class="jsMarkAsDone"
                            data-endpoint="{link application='todolist' controller='TodoMarkAsDone' object=$todo}{/link}"
                            data-is-done="{if $todo->isDone()}1{else}0{/if}"
                            data-object-id="{@$todo->todoID}"
                        >
                            <span>
                                {if $todo->isDone()}
                                    {lang}todolist.action.markAsUndone{/lang}
                                {else}
                                    {lang}todolist.action.markAsDone{/lang}
                                {/if}
                            </span>
                        </a>
                    </li>
                {/if}
                {if $todo->canDelete()}
                    <li class="jsOnly">
                        <a href="#" class="jsDelete">
                            <span>{lang}wcf.global.button.delete{/lang}</span>
                        </a>
                    </li>
                {/if}

                {event name='afterDropdownItems'}
            {/content}
    {/capture}
{/hascontent}

{capture assign='sidebarRight'}
    {hascontent}
        <section class="box">
            <h2 class="boxTitle">{lang}todolist.general.info{/lang}</h2>
        
            <div class="boxContent">
                {content}
                    {if $todo->currentEditor}
                        <dl>
                            <dt>{lang}todolist.column.currentEditor{/lang}</dt>
                            <dd itemprop="author" itemscope itemtype="http://schema.org/Person">{user object=$todo->getCurrentEditorProfile()}</dd>
                        </dl>
                    {/if}
     
                    {event name='todoInfoBox'}
                {/content}
            </div>
        </section>
    {/hascontent}

    {event name='todoSidebarBoxes'}
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
    require(['JulianPfeil/Todolist/Ui/Todo/Action/Handler/Delete'], ({ Delete }) => {
        const deleteUser = document.querySelector(".jsDelete");
        if (deleteUser !== null) {
            // We cannot use the DeleteAction, because the Delete Action is only usable for
            // dropdown menues.
            deleteUser.addEventListener("click", (event) => {
                const deleteAction = new Delete([{#$todo->todoID}], () => {
                    window.location.href = "{link application='todolist' controller='TodoList' encode=false}{/link}";
                });

                deleteAction.delete();
            });
        }      
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
