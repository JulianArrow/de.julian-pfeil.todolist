{capture assign='contentTitle'}{lang}todolist.general.list{/lang}{/capture}

{assign var='additionalLinkParameters' value=''}
{if $categoryID}{capture append='additionalLinkParameters'}categoryID={$categoryID}&{/capture}{/if}
{if $sortField}{capture append='additionalLinkParameters'}sortField={$sortField}&{/capture}{/if}
{if $sortOrder}{capture append='additionalLinkParameters'}sortOrder={$sortOrder}&{/capture}{/if}
{if $currentEditor}{capture append='additionalLinkParameters'}currentEditor={$currentEditor}&{/capture}{/if}
{event name='additionalLinkParameters'}
{assign var=additionalLinkParameters value=$additionalLinkParameters|substr:0:-1}

{capture assign='headContent'}
    {if $pageNo < $pages}
        <link rel="next" href="{link application='todolist' controller='TodoList'}pageNo={@$pageNo+1}{@$additionalLinkParameters}{if $isDone}&isDone={$isDone}{/if}{/link}">
    {/if}
    {if $pageNo > 1}
        <link rel="prev" href="{link application='todolist' controller='TodoList'}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{@$additionalLinkParameters}{if $isDone}&isDone={$isDone}{/if}{/link}">
    {/if}
    <link rel="canonical" href="{link application='todolist' controller='TodoList'}{if $pageNo > 1}pageNo={@$pageNo}{/if}{@$additionalLinkParameters}{if $isDone}&isDone={$isDone}{/if}{/link}">
{/capture}

{capture assign='contentHeaderNavigation'}
    {include file='todoAddButton' application='todolist' listItem=true}
{/capture}

{capture assign='sidebarRight'}
    {include file='todoListSidebar' application='todolist'}
{/capture}

{capture assign='contentInteractionButtons'}
    {if $categoryID|isset && $categoryID > 0}
        {include file='__userObjectWatchButton' isSubscribed=$category->isSubscribed() objectType='de.julian-pfeil.todolist.todo.category' objectID=$category->categoryID}
    {/if}
{/capture}

{include file='header'}

{assign var='pagesLinkString' value="pageNo=%d$additionalLinkParameters"}
{if $isDone}{capture append='pagesLinkString'}&isDone={$isDone}{/capture}{/if}

{hascontent}
    <div class="paginationTop">
        {content}
            {pages print=true assign=pagesLinks application='todolist' controller='TodoList' link="$pagesLinkString"}
        {/content}
    </div>
{/hascontent}

<div class="section">
    {include file='todoListStructure' application='todolist'}
</div>

<footer class="contentFooter">
    {hascontent}
        <div class="paginationBottom">
            {content}{@$pagesLinks}{/content}
        </div>
    {/hascontent}
    
    {hascontent}
        <nav class="contentFooterNavigation">
            <ul>
                {content}
                    {include file='todoAddButton' application='todolist' listItem=true}

                    {event name='contentFooterNavigation'}
                {/content}
            </ul>
        </nav>
    {/hascontent}
</footer>

<script data-relocate="true">
    require(['JulianPfeil/Todolist/Ui/Todo/MarkAsDone', 'Language'], ({ MarkAsDone }, Language) => {
        new MarkAsDone();

        Language.addObject({	
            'todolist.general.isDone':'     {jslang}todolist.general.isDone{/jslang}',
            'todolist.general.isUndone':'   {jslang}todolist.general.isUndone{/jslang}'
        });
    });
</script>

{event name='additionalJavascript'}

{include file='footer'}
