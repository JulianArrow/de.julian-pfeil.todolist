
<div class="todoContent section{if $tabMenu == 1} tabMenuContent{/if}">
    <div class="section">
        {if $objects|count}
            <ul class="containerList">
                {foreach from=$objects item=todo}
                    <li>
                        <div class="box48">
                            <a href="{link controller='User' object=$todo->getUserProfile()}{/link}" title="{$todo->username}" aria-hidden="true">{@$todo->getUserProfile()->getAvatar()->getImageTag(48)}</a>
                            
                            <div class="details">
                                <div class="containerHeadline">
                                    <h3>{user object=$todo->getUserProfile()}</h3>
                                    <small>{@$todo->time|time}</small>
                                </div>
                                
                                <p>{@$todo}</p>
                            </div>
                        </div>
                    </li>
                {/foreach}
            </ul>
        {else}
            <p class="info">{lang}wcf.global.noItems{/lang}</p>
        {/if}
    </div>
</div>