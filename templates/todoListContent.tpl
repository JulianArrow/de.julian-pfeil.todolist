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
                                    <span class="icon icon16 jsMarkAsDone fa-check-square-o" data-object-id="{@$todo->todoID}" data-tooltip="{lang}todolist.general.isDone{/lang}" aria-label="{lang}todolist.general.isDone{/lang}"></span>
                                {else}
                                    <span class="icon icon16 jsMarkAsDone fa-square-o" data-object-id="{@$todo->todoID}" data-tooltip="{lang}todolist.general.isUndone{/lang}" aria-label="{lang}todolist.general.isUndone{/lang}"></span>
                                {/if}
                                
                                
                                <div class="todoContainerMetaData">
                                    <a href="{$todo->getLink()}" title="{$todo->getPlainExcerpt()}">{$todo->getTitle()}</a>
                                
                                    <small class="separatorLeft">
                                        <span class="icon icon16 fa-user"></span>

                                        {if $todo->userID}
                                            {user object=$todo->getUserProfile()}
                                        {else}
                                            <span>{$todo->username}</span>
                                        {/if}
                                    </small>
                                    
                                    <small class="separatorLeft">
                                        {if $todo->time < $todo->lastEditTime}
                                            <span class="icon icon16 fa-pencil"></span>
                                            {@$todo->lastEditTime|time}
                                        {else}
                                            <span class="icon icon16 fa-clock-o"></span>
                                            {@$todo->time|time}
                                        {/if}
                                    </small>

                                    <small class="separatorLeft">
                                        <span class="icon icon16 fa-eye"></span>
                                        {$todo->views}
                                    </small>       

                                    {if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $todo->cumulativeLikes} 
                                        <small class="separatorLeft">
                                            {include file='__topReaction' cachedReactions=$todo->cachedReactions render='tiny'}
                                        </small>
                                    {/if}
                                    
                                    {if $todo->enableComments && $todo->comments > 0}
                                        <small class="separatorLeft">
                                            <span class="icon icon16 fa-comments"></span> 
                                            {@$todo->comments|shortUnit}
                                        </small>
                                    {/if}
                                    
                                    {event name='containerHeadline'}
                                </div>
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
