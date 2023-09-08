{if $todo->enableComments}
    {if $commentList|count || $commentCanAdd}
        <div id="commentsTab" class="tabMenuContent">
            {include file='comments' commentContainerID='todoCommentList' commentObjectID=$todo->todoID}
        </div>
    {/if}
{/if}