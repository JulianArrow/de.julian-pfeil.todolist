{if $canAddTodoInAnyCategory}
    {if $listItem|isset && $listItem}<li>{/if}
        <a href="{if $category|isset && $category->canAddTodo()}{link application='todolist' controller='TodoAdd' id=$category->categoryID}{/link}{else}{link application='todolist' controller='TodoAdd'}{/link}{/if}" class="button{if $classes|isset && $classes} {$classes}{/if}" id="todoAddButton">
            {icon name='plus'}
            <span>{lang}todolist.action.add{/lang}</span>
        </a>
    {if $listItem|isset && $listItem}</li>{/if}
{/if}
