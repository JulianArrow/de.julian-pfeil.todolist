{assign var=tabMenu value='0'}

{hascontent}
    {assign var=tabMenu value='1'}

    <div class="section{if $pageFrom == 'todo'} tabMenuContainer{/if}">

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
                    {event name='tabMenuTabs'}
                {/content}
            </ul>
        </nav>
{/hascontent}

{if $pageFrom == 'todo'}
    {include file='todoContent' application='todolist'}
{/if}

{event name='pageContents'}

{if $tabMenu == 1}</div>{/if}