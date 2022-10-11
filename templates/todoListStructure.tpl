{if $viewableCategoryList|count}
    <nav class="tabMenu">
        <ul>
            <li {if $isDone == '0'}class ="active"{/if}><a href="{link application='todolist' controller='TodoList'}{if $pageNo > 1}pageNo={@$pageNo}&{/if}{@$additionalLinkParameters}&isDone=0{/link}">{lang}todolist.general.isUndone{/lang}{if $isDone == '0'} <span class="badge">{#$items}</span>{/if}</a></li>
            <li {if $isDone == '1'}class ="active"{/if}><a href="{link application='todolist' controller='TodoList'}{if $pageNo > 1}pageNo={@$pageNo}&{/if}{@$additionalLinkParameters}&isDone=1{/link}">{lang}todolist.general.isDone{/lang}{if $isDone == '1'}  <span class="badge">{#$items}</span>{/if}</a></li>
    
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
    <p class="info">{lang}todolist.category.info.noCategories{/lang}</p>
{/if}