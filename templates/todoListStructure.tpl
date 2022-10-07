{if $viewableCategoryList|count}
    <nav class="tabMenu">
        <ul>
            <li {if $isDone == '0'}class ="active"{/if}><a href="{link application='todolist' controller='TodoList'}sortField={$sortField}&sortOrder={$sortOrder}{if $pageNo > 1}&pageNo={@$pageNo}{/if}&isDone=0{/link}">{lang}todolist.general.undone{/lang}{if $isDone == '0'} <span class="badge">{#$items}</span>{/if}</a></li>
            <li {if $isDone == '1'}class ="active"{/if}><a href="{link application='todolist' controller='TodoList'}sortField={$sortField}&sortOrder={$sortOrder}{if $pageNo > 1}&pageNo={@$pageNo}{/if}&isDone=1{/link}">{lang}todolist.general.done{/lang}{if $isDone == '1'}  <span class="badge">{#$items}</span>{/if}</a></li>
            
            {event name='tabMenuTabs'}
        </ul>
    </nav>

    <div class="tabMenuContent">
        <div class="section sectionContainerList">
            {if $items}
                {include file='todoListContent' application='todolist'}
            {else}
                <p class="info">{lang}wcf.global.noItems{/lang}</p>
            {/if}
        </div>
    </div>
{else}
    <p class="info">{lang}todolist.general.noCategories{/lang}</p>
{/if}