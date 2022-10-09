<ul class="commentList containerList todoList jsObjectActionContainer jsReloadPageWhenEmpty" {*
    *}data-object-action-class-name="todolist\data\todo\TodoAction"{*
*}>
    {foreach from=$objects item=todo}
        <li class="comment todo jsObjectActionObject todoHeader" 
            data-object-id="{@$todo->todoID}"
            data-todo-id="{@$todo->todoID}"
            data-is-done="{if $todo->isDone()}true{else}false{/if}"
            data-can-mark-as-done="{if $todo->canEdit()}1{else}0{/if}"
        >
            <div class="box48{if $__wcf->getUserProfileHandler()->isIgnoredUser($todo->userID, 2)} ignoredUserContent{/if}">
                <div class="commentContentContainer">
                    <div class="commentContent">
                        <div class="containerHeadline">
                            <h3>
                                {if $todo->isDone()}
                                    <span class="icon icon16 jsMarkAsDone fa-check-square-o" data-object-id="{@$todo->todoID}" data-tooltip="{lang}todolist.general.done{/lang}" aria-label="{lang}todolist.general.done{/lang}"></span>
                                {else}
                                    <span class="icon icon16 jsMarkAsDone fa-square-o" data-object-id="{@$todo->todoID}" data-tooltip="{lang}todolist.general.undone{/lang}" aria-label="{lang}todolist.general.undone{/lang}"></span>
                                {/if}
                                
                            
                                <a href="{$todo->getLink()}" title="{$todo->getExcerpt()}">{$todo->getTitle()}</a>
                            
                                <small class="separatorLeft">
                                    <span class="icon icon16 fa-user"></span>

                                    {if $todo->userID}
                                        {user object=$todo->getUserProfile()}
                                    {else}
                                        <span>{$todo->username}</span>
                                    {/if}
                                </small>
                                
                                <small class="separatorLeft">
                                    {if $todo->time == $todo->lastEditTime}
                                        <span class="icon icon16 fa-clock-o"></span>
                                        {@$todo->time|time}
                                    {else}
                                        <span class="icon icon16 fa-pencil"></span>
                                        {@$todo->lastEditTime|time}
                                    {/if}
                                </small>

                                {if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $todo->cumulativeLikes} 
                                    <small class="separatorLeft">
                                        {include file='__topReaction' cachedReactions=$todo->cachedReactions render='tiny'}
                                    </small>
                                {/if}

                                {if "TODOLIST_COMMENTS_PLUGIN"|defined}
                                    {if $todo->enableComments && $todo->comments > 0}
                                        <small class="separatorLeft">
                                            <span class="icon icon16 fa-comments"></span> 
                                            {lang}todolist.comment.metaData{/lang}
                                        </small>
                                    {/if}
                                {/if}

                                {if "TODOLIST_LABELS_PLUGIN"|defined && $todo->hasLabels()}
                                    <small class="separatorLeft"></small>
                                        <ul class="labelList">
                                            {foreach from=$todo->getLabels() item=label}
                                                <li>{@$label->render()}</li>
                                            {/foreach}
                                        </ul>
                                {/if}
                                
                                {event name='containerHeadline'}
                            </h3>
                        </div>
                        
                        <nav class="jsMobileNavigation buttonGroupNavigation">
                            <ul class="buttonList iconList">
                                {if $todo->canEdit()}
                                    <li class="jsOnly">
                                        <a href="{link application='todolist' controller='TodoEdit' object=$todo}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsEditInformation jsTooltip">
                                            <span class="icon icon16 fa-pencil"></span>
                                            <span class="invisible">{lang}wcf.global.button.edit{/lang}</span>
                                        </a>
                                    </li>
                                {/if}
                                {if $todo->canDelete()}
                                    <li class="jsOnly">
                                        <a href="#" title="{lang}wcf.global.button.delete{/lang}" class="jsObjectAction jsTooltip" data-object-action="delete" data-confirm-message="{lang}todolist.action.confirmDelete{/lang}">
                                            <span class="icon icon16 fa-times"></span>
                                            <span class="invisible">{lang}wcf.global.button.delete{/lang}</span>
                                        </a>
                                    </li>
                                {/if}
                                
                                {event name='informationOptions'}
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </li>
    {/foreach}
</ul>
