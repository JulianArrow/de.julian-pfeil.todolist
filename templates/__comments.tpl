{if $todo->enableComments}
    {if $commentList|count || $commentCanAdd}
        <div id="commentsTab" class="tabMenuContent">
            {include file='__commentJavaScript' commentContainerID='todoCommentList'}
            
            <ul id="todoCommentList" class="commentList containerList" {*
                *}data-can-add="{if $commentCanAdd}true{else}false{/if}" {*
                *}data-object-id="{@$todo->todoID}" {*
                *}data-object-type-id="{@$commentObjectTypeID}" {*
                *}data-comments="{if $todo->comments}{@$commentList->countObjects()}{else}0{/if}" {*
                *}data-last-comment-time="{@$lastCommentTime}" {*
            *}>
                {include file='commentListAddComment' wysiwygSelector='todoCommentListAddComment'}
                {include file='commentList'}
            </ul>
        </div>
    {/if}
{/if}