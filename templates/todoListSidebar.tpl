<section class="box">
<form method="post" action="{link application='todolist' controller='TodoList'}{/link}">
    <h2 class="boxTitle">{lang}todolist.general.filter{/lang}</h2>
    
    <div class="boxContent">
        <dl>
            <dt></dt>
            <dd>
                <select id="categoryID" name="categoryID">
                    <option value=""{if $categoryID == ''} selected{/if}>{lang}todolist.general.allCategories{/lang}</option>
                    
                    {foreach from=$viewableCategoryList item=categoryItem}  
                        <option value="{@$categoryItem->categoryID}"{if $categoryID == $categoryItem->categoryID} selected{/if}>{$categoryItem->getTitle()}</option>
                    {/foreach}
                </select>
            </dd>
        </dl>
        
        <div class="formSubmit">
            <input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
            {if $isDone}<input type="hidden" name="isDone" value="{$isDone}">{/if}
            {if $sortField}<input type="hidden" name="sortField" value="{$sortField}">{/if}
            {if $sortOrder}<input type="hidden" name="sortOrder" value="{$sortOrder}">{/if}
            
            {if "TODOLIST_LABELS_PLUGIN"|defined}
                {foreach from=$labelGroups item=labelGroup}
                    {foreach from=$labelGroup item=label}
                        {if $labelIDs[$labelGroup->groupID]|isset && $labelIDs[$labelGroup->groupID] == $label->labelID}
                            <input type="hidden" name="labelIDs[{@$labelGroup->groupID}]" value="{@$label->labelID}">
                        {/if}
                    {/foreach}
                {/foreach}
            {/if}
        </div>
    </div>
</form>
</section>

{if !$labelGroups|empty && "TODOLIST_LABELS_PLUGIN"|defined}
    <section class="box">
        <form id="todolistLabelForm" method="post" action="{link application='todolist' controller='Todolist'}{/link}">
            <h2 class="boxTitle">{lang}wcf.label.label{/lang}</h2>

            <div class="boxContent">
                <dl>
                    {foreach from=$labelGroups item=labelGroup}
                        {if $labelGroup|count}
                            <dt><label>{$labelGroup->getTitle()}</label></dt>
                            <dd>
                                <ul class="labelList jsOnly">
                                    <li class="dropdown labelChooser" id="labelGroup{@$labelGroup->groupID}" data-group-id="{@$labelGroup->groupID}">
                                        <div class="dropdownToggle" data-toggle="labelGroup{@$labelGroup->groupID}"><span class="badge label">{lang}wcf.label.none{/lang}</span></div>
                                        <div class="dropdownMenu">
                                            <ul class="scrollableDropdownMenu">
                                                {foreach from=$labelGroup item=label}
                                                    <li data-label-id="{@$label->labelID}"><span><span class="badge label{if $label->getClassNames()} {@$label->getClassNames()}{/if}">{$label->getTitle()}</span></span></li>
                                                {/foreach}
                                            </ul>
                                        </div>
                                    </li>
                                </ul>
                                <noscript>
                                    {foreach from=$labelGroups item=labelGroup}
                                        <select name="labelIDs[{@$labelGroup->groupID}]">
                                            <option value="0">{lang}wcf.label.none{/lang}</option>
                                            <option value="-1">{lang}wcf.label.withoutSelection{/lang}</option>
                                            {foreach from=$labelGroup item=label}
                                                <option value="{@$label->labelID}"{if $labelIDs[$labelGroup->groupID]|isset && $labelIDs[$labelGroup->groupID] == $label->labelID} selected{/if}>{$label->getTitle()}</option>
                                            {/foreach}
                                        </select>
                                    {/foreach}
                                </noscript>
                            </dd>
                        {/if}
                    {/foreach}
                </dl>
                <div class="formSubmit">
                    <input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
                    {if $categoryID}<input type="hidden" name="categoryID" value="{$categoryID}">{/if}
                    {if $sortField}<input type="hidden" name="sortField" value="{$sortField}">{/if}
                    {if $sortOrder}<input type="hidden" name="sortOrder" value="{$sortOrder}">{/if}
                    {if $isDone}<input type="hidden" name="isDone" value="{$isDone}">{/if}
                </div>
            </div>
        </form>
    </section>
{/if}

<section class="box">
<form method="post" action="{link application='todolist' controller='TodoList'}{/link}">
    <h2 class="boxTitle">{lang}wcf.global.sorting{/lang}</h2>
    
    <div class="boxContent">
        <dl>
            <dt></dt>
            <dd>
                <select id="sortField" name="sortField">
                    <option value="time"{if $sortField == 'time'} selected{/if}>{lang}todolist.column.time{/lang}</option>
                    <option value="todoName"{if $sortField == 'todoName'} selected{/if}>{lang}todolist.column.todoName{/lang}</option>
                    
                    {if MODULE_LIKE}
                        <option value="cumulativeLikes"{if $sortField == 'cumulativeLikes'} selected{/if}>{lang}wcf.like.cumulativeLikes{/lang}</option>
                    {/if}

                    {if "TODOLIST_COMMENTS_PLUGIN"|defined}
                        <option value="comments"{if $sortField == 'comments'} selected{/if}>{lang}todolist.comment.plural{/lang}</option>
                    {/if}

                    {event name='sortField'}
                </select>

                <select name="sortOrder" style="margin-top: 2px">
                    <option value="ASC"{if $sortOrder == 'ASC'} selected{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
                    <option value="DESC"{if $sortOrder == 'DESC'} selected{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
                </select>
            </dd>
        </dl>
        
        <div class="formSubmit">
            <input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
            {if $categoryID}<input type="hidden" name="categoryID" value="{$categoryID}">{/if}
            {if $isDone}<input type="hidden" name="isDone" value="{$isDone}">{/if}
            
            {if "TODOLIST_LABELS_PLUGIN"|defined}
                {foreach from=$labelGroups item=labelGroup}
                    {foreach from=$labelGroup item=label}
                        {if $labelIDs[$labelGroup->groupID]|isset && $labelIDs[$labelGroup->groupID] == $label->labelID}
                            <input type="hidden" name="labelIDs[{@$labelGroup->groupID}]" value="{@$label->labelID}">
                        {/if}
                    {/foreach}
                {/foreach}
            {/if}
        </div>
    </div>
</form>
</section>