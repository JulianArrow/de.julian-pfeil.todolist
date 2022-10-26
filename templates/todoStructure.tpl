{assign var=tabMenu value='0'}

{hascontent}
    {assign var=tabMenu value='1'}

    <div class="section tabMenuContainer">

        <nav class="tabMenu">
            <ul>
                <li><a href="{@$__wcf->getAnchor('generalTab')}">{lang}todolist.general.title{/lang}</a></li>

                {content}
                    {if $todo->enableComments}
                        {if $commentList|count || $commentCanAdd}
                            <li>
                                <a href="{@$__wcf->getAnchor('commentsTab')}">
                                    {lang}todolist.comment.plural{/lang}{if $todo->comments} <span class="badge">{#$todo->comments}</span>{/if}
                                </a>
                            </li>
                        {/if}
                    {/if}
                
                    {event name='tabMenuTabs'}
                {/content}
            </ul>
        </nav>
{/hascontent}

{include file='todoContent' application='todolist'}

{event name='pageContents'}

{if $tabMenu == 1}</div>{/if}