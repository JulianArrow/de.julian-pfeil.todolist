<section class="box">
    <form method="post" action="{link application='todolist' controller='TodoList'}{/link}">
        <h2 class="boxTitle">{lang}todolist.category.title{/lang}</h2>

        <div class="boxContent">
            <dl>
                <dt></dt>
                <dd>
                    <select id="categoryID" name="categoryID">
                        <option{if $categoryID == ''} selected{/if} value="">{lang}todolist.category.all{/lang}</option>
                            {foreach from=$viewableCategoryList item=categoryItem}
                                <option value="{@$categoryItem->categoryID}" {if $categoryID == $categoryItem->categoryID}
                                    selected{/if}>{$categoryItem->getTitle()}</option>
                            {/foreach}
                    </select>
                </dd>
            </dl>

            <div class="formSubmit">
                <input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
                {if $isDone|isset}<input type="hidden" name="isDone" value="{$isDone}">{/if}
                {if $sortField|isset}<input type="hidden" name="sortField" value="{$sortField}">{/if}
                {if $sortOrder|isset}<input type="hidden" name="sortOrder" value="{$sortOrder}">{/if}
                {if $currentEditor|isset}<input type="hidden" name="currentEditor" value="{$currentEditor}">{/if}

                {event name='afterCategoryHiddenFields'}
            </div>
        </div>
    </form>
</section>

<section class="box">
    <form method="post" action="{link application='todolist' controller='TodoList'}{/link}">
        <h2 class="boxTitle">{lang}wcf.global.filter{/lang}</h2>

        <div class="boxContent">
            <dl>
                <dt></dt>
                <dd>
                    <input type="text" id="currentEditor" name="currentEditor" value="{$currentEditor}"
                        placeholder="{lang}todolist.column.currentEditor{/lang}" autocomplete="off" />
                </dd>
            </dl>

            <div class="formSubmit">
                <input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
                {if $isDone|isset}<input type="hidden" name="isDone" value="{$isDone}">{/if}
                {if $sortField|isset}<input type="hidden" name="sortField" value="{$sortField}">{/if}
                {if $sortOrder|isset}<input type="hidden" name="sortOrder" value="{$sortOrder}">{/if}
                {if $categoryID|isset}<input type="hidden" name="categoryID" value="{$categoryID}">{/if}

                {event name='afterCurrentEditorHiddenFields'}
            </div>
        </div>
    </form>
</section>

<script data-relocate="true">
    $(function() {
        require(['WoltLabSuite/Core/Ui/User/Search/Input'], function(UiUserSearchInput) {
            new UiUserSearchInput(elById('currentEditor'), {
                includeUserGroups: false,
                preventSubmit: true
            });
        });
    });
</script>

{event name='sidebarBoxes'}

<section class="box">
    <form method="post" action="{link application='todolist' controller='TodoList'}{/link}">
        <h2 class="boxTitle">{lang}wcf.global.sorting{/lang}</h2>

        <div class="boxContent">
            <dl>
                <dt></dt>
                <dd>
                    <select id="sortField" name="sortField">
                        <option value="time" {if $sortField == 'time'} selected{/if}>{lang}todolist.column.time{/lang}
                        </option>
                        <option value="lastEditTime" {if $sortField == 'lastEditTime'} selected{/if}>
                            {lang}todolist.column.lastEditTime{/lang}</option>
                        <option value="views" {if $sortField == 'views'} selected{/if}>
                            {lang}todolist.column.views.plural{/lang}</option>
                        <option value="comments" {if $sortField == 'comments'} selected{/if}>
                            {lang}todolist.comment.plural{/lang}</option>
                        {if MODULE_LIKE}
                            <option value="cumulativeLikes" {if $sortField == 'cumulativeLikes'} selected{/if}>
                                {lang}wcf.like.cumulativeLikes{/lang}</option>
                        {/if}
                        <option value="todoName" {if $sortField == 'todoName'} selected{/if}>
                            {lang}todolist.column.todoName{/lang}</option>

                        {event name='sortField'}
                    </select>

                    <select name="sortOrder" style="margin-top: 2px">
                        <option value="ASC" {if $sortOrder == 'ASC'} selected{/if}>
                            {lang}wcf.global.sortOrder.ascending{/lang}</option>
                        <option value="DESC" {if $sortOrder == 'DESC'} selected{/if}>
                            {lang}wcf.global.sortOrder.descending{/lang}</option>
                    </select>
                </dd>
            </dl>

            <div class="formSubmit">
                <input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
                {if $categoryID|isset}<input type="hidden" name="categoryID" value="{$categoryID}">{/if}
                {if $isDone|isset}<input type="hidden" name="isDone" value="{$isDone}">{/if}
                {if $currentEditor|isset}<input type="hidden" name="currentEditor" value="{$currentEditor}">{/if}

                {event name='afterSortingHiddenFields'}
            </div>
        </div>
    </form>
</section>