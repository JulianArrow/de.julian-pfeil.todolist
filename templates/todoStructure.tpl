{assign var=tabMenu value='0'}

{hascontent}
{assign var=tabMenu value='1'}

<div class="section tabMenuContainer">

	<nav class="tabMenu">
		<ul>
			<li><a href="
			{if $pageFrom == 'todo'}
            	{@$__wcf->getAnchor('generalTab')}
			{else}
				{$todo->getLink()}#generalTab
			{/if}
            ">{lang}todolist.general.title{/lang}</a></li>

            {content}
                {if "TODOLIST_COMMENTS_PLUGIN"|defined}
                    {if $todo->enableComments}
                        {if $commentList|count || $commentCanAdd}
                            <li>
                                <a href="
								{if $pageFrom == 'todo'}
									{@$__wcf->getAnchor('commentsTab')}
								{else}
									{$todo->getLink()}#commentsTab
								{/if}
                                ">
                                {lang}todolist.comment.plural{/lang}{if $todo->comments} <span class="badge">{#$todo->comments}</span>{/if}
                                </a>
                            </li>
                        {/if}
                    {/if}
                {/if}

                {if "TODOLIST_MODIFICATION_LOG_PLUGIN"|defined}
                    {if $pageFrom == 'todo' && $todo->canEdit() || !$pageFrom == 'todo'}
                        <li>
                            <a href="
							{if $pageFrom == 'todo'}
								{link application='todolist' controller='TodoLog' todoID=$todo->todoID}
							{else}
								#
							{/if}
                            ">
                            {lang}todolist.modLog.title{/lang}
                            </a>
                        </li>
                    {/if}
                {/if}
                
                {event name='tabMenuTabs'}
            {/content}
		</ul>
	</nav>
{/hascontent}

{if $pageFrom == 'todo'}
	{include file='todoContent' application='todolist'}
{else}
	{include file='todoLogContent' application='todolist'}
{/if}

{if $tabMenu == 1}</div>{/if}