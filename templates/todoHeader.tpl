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
                    {icon name='user'}
                    
                    {if $todo->userID}
                        {user object=$todo->getUserProfile()}
                    {else}
                        <span>{$todo->username}</span>
                    {/if}
                </li>
                
                <li>
                    {icon name='clock'}
                    {@$todo->time|time}
                    <meta itemprop="dateCreated" content="{@$todo->time|date:'c'}">
                    <meta itemprop="datePublished" content="{@$todo->time|date:'c'}">
                    <meta itemprop="operatingSystem" content="N/A">
                </li>

                {if $todo->time != $todo->lastEditTime}
                    <li class="jsTooltip" title="{lang}todolist.column.lastEditTime{/lang}">
                        {icon name='pencil'}
                        <span><a href="{link controller='Todo' object=$todo}{/link}" class="permalink">{@$todo->lastEditTime|time}</a></span>
                        <meta itemprop="dateModified" content="{@$todo->lastEditTime|date:'c'}">
                    </li>
                {/if}

                <li>
                    {icon name='eye'}
                    {$todo->views} {lang}todolist.column.views{/lang}
                </li>

            {if $todo->enableComments}
                <li>
                    {icon name='comments'}
                    {lang}todolist.comment.metaData{/lang}
                </li>
            {/if}
                
                {event name='afterMetaData'}
                
                <li>
                    <button
                        class="jsMarkAsDone"
                        data-endpoint="{link application='todolist' controller='TodoMarkAsDone' object=$todo}{/link}"
                        data-is-done="{if $todo->isDone()}1{else}0{/if}" 
                        data-object-id="{@$todo->todoID}"
                    >
                        {if $todo->isDone()}
                            <span data-tooltip="{lang}todolist.general.isDone{/lang}" aria-label="{lang}todolist.general.isDone{/lang}">{icon name='check-square'}</span>
                            <span class="doneTitle">{lang}todolist.general.isDone{/lang}</span>
                        {else}
                            <span data-tooltip="{lang}todolist.general.isUndone{/lang}" aria-label="{lang}todolist.general.isUndone{/lang}">{icon name='square'}</span>
                            <span class="doneTitle">{lang}todolist.general.isUndone{/lang}</span>
                        {/if}
                    </button>
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

    <script data-relocate="true">
        require(['JulianPfeil/Todolist/Ui/Todo/MarkAsDone', 'Language'], ({ MarkAsDone }, Language) => {
            new MarkAsDone();

            Language.addObject({	
                'todolist.action.markAsDone':'  {jslang}todolist.action.markAsDone{/jslang}',
                'todolist.action.markAsUndone':'{jslang}todolist.action.markAsUndone{/jslang}',
                'todolist.general.isDone':'     {jslang}todolist.general.isDone{/jslang}',
                'todolist.general.isUndone':'   {jslang}todolist.general.isUndone{/jslang}'
            });
        });
    </script>