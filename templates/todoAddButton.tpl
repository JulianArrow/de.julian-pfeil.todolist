{if $canAddTodoInAnyCategory}
    <li>
        <a href="
        {if $todo|isset && $todo->category->canAddTodo()}
            {link application='todolist' controller='TodoAdd' categoryID=$todo->categoryID}{/link}
        {elseif $category|isset && $category->canAddTodo()}
            {link application='todolist' controller='TodoAdd' categoryID=$category->categoryID}{/link}
        {else}
            {link application='todolist' controller='TodoAdd'}{/link}
        {/if}
        " class="button" id="todoAddButton">
            <span class="icon icon16 fa-plus"></span>
            <span>{lang}todolist.action.add{/lang}</span>
        </a>
    </li>
{/if}