{if $canAddTodoInAnyCategory}
    <li>
        <a href="
        {if $category|isset && $category->canAddTodo()}
            {link application='todolist' controller='TodoAdd' id=$category->categoryID}{/link}
        {else}
            {link application='todolist' controller='TodoAdd'}{/link}
        {/if}
        " class="button{if $smallAddButton|isset && $smallAddButton} small{/if}" id="todoAddButton">
            <span class="icon icon16 fa-plus"></span>
            <span>{lang}todolist.action.add{/lang}</span>
        </a>
    </li>
{/if}