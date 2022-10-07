{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $todoLikeData|isset}
    <div class="section">
        <div class="section reactionSummaryList">{include file="reactionSummaryList" reactionData=$todoLikeData objectType="de.julian-pfeil.todolist.likeableTodo" objectID=$todo->todoID}</div>
    </div>
{/if}