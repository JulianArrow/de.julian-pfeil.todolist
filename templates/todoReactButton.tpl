{if MODULE_LIKE && $__wcf->getUser()->userID && $__wcf->getSession()->getPermission('user.like.canViewLike') && $todo->userID != $__wcf->user->userID}
    <li>
        <a href="#" id="todoReactButton" 
            class="reactButton jsTooltip button{if $todoLikeData[$todo->todoID]|isset && $todoLikeData[$todo->todoID]->reactionTypeID} active{/if}" 
            title="{lang}wcf.reactions.react{/lang}" 
            data-reaction-type-id="{if $todoLikeData[$todo->todoID]|isset && $todoLikeData[$todo->todoID]->reactionTypeID}{$todoLikeData[$todo->todoID]->reactionTypeID}{else}0{/if}
        ">
            <span class="icon icon16 fa-smile-o"></span>
            <span class="invisible">{lang}wcf.reactions.react{/lang}</span>
        </a>
    </li>
{/if}
                    