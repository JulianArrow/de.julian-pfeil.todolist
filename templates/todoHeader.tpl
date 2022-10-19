<header class="contentHeader todoHeader"
        data-object-id="{@$todo->todoID}"
        data-todo-id="{@$todo->todoID}"
        data-is-done="{if $todo->isDone()}true{else}false{/if}"
        data-can-mark-as-done="{if $todo->canEdit()}1{else}0{/if}"
    >
        <div class="contentHeaderTitle">
            <h1 class="contentTitle">{@$contentTitle}{if !$contentTitleBadge|empty} {@$contentTitleBadge}{/if}</h1>
            {if !$contentDescription|empty}<p class="contentHeaderDescription">{@$contentDescription}</p>{/if}
            
            <ul class="inlineList contentHeaderMetaData">
                {event name='beforeMetaData'}
                
                <li itemprop="author" itemscope itemtype="http://schema.org/Person">
                    <span class="icon icon16 fa-user"></span>
                    
                    {if $todo->userID}
                        {user object=$todo->getUserProfile()}
                    {else}
                        <span>{$todo->username}</span>
                    {/if}
                </li>
                
                <li>
                    <span class="icon icon16 fa-clock-o"></span>
                    {@$todo->time|time}
                    <meta itemprop="dateCreated" content="{@$todo->time|date:'c'}">
                    <meta itemprop="datePublished" content="{@$todo->time|date:'c'}">
                    <meta itemprop="operatingSystem" content="N/A">
                </li>

                {if $todo->time != $todo->lastEditTime}
                    <li class="jsTooltip" title="{lang}todolist.column.lastEditTime{/lang}">
                        <span class="icon icon16 fa-pencil"></span>
                        <span><a href="{link controller='Todo' object=$todo}{/link}" class="permalink">{@$todo->lastEditTime|time}</a></span>
                        <meta itemprop="dateModified" content="{@$todo->lastEditTime|date:'c'}">
                    </li>
                {/if}

                <li>
                    <span class="icon icon16 fa-eye"></span>
                    {$todo->views} {lang}todolist.column.views{/lang}
                </li>
                
                {event name='afterMetaData'}
                
                <li class="jsMarkAsDone" data-object-id="{@$todo->todoID}">
                    {if $todo->isDone()}
                        <span class="icon icon16 fa-check-square-o" data-tooltip="{lang}todolist.general.isDone{/lang}" aria-label="{lang}todolist.general.isDone{/lang}"></span>
                        <span class="doneTitle">{lang}todolist.general.isDone{/lang}</span>
                    {else}
                        <span class="icon icon16 fa-square-o" data-tooltip="{lang}todolist.general.isUndone{/lang}" aria-label="{lang}todolist.general.isUndone{/lang}"></span>
                        <span class="doneTitle">{lang}todolist.general.isUndone{/lang}</span>
                    {/if}
                </li>
            </ul>
        </div>
        
        {hascontent}
            <nav class="contentHeaderNavigation">
                <ul>
                    {content}
                        {if !$contentHeaderNavigation|empty}{@$contentHeaderNavigation}{/if}
                        
                        {event name='contentHeaderNavigation'}
                    {/content}
                </ul>
            </nav>
        {/hascontent}
    </header>