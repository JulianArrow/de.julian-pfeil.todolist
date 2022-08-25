{if $todo->enableComments}
    {if $commentList|count || $commentCanAdd}
        <li>
            <a href="{@$__wcf->getAnchor('commentsTab')}">
              {lang}todolist.comment.plural{/lang}{if $todo->comments} <span class="badge">{#$todo->comments}</span>{/if}
            </a>
        </li>
    {/if}
{/if}