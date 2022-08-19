{capture assign='pageTitle'}{$todo} - {lang}todolist.general.list{/lang}{/capture}

{capture assign='contentTitle'}{lang}todolist.general.todo{/lang}{/capture}

{capture assign='contentHeader'}
	<header class="contentHeader todoHeader"
		data-object-id="{@$todo->todoID}"
		data-todo-id="{@$todo->todoID}"
		data-is-done="{if $todo->isDone()}true{else}false{/if}"
		data-can-mark-as-done="{if $todo->canEdit()}1{else}0{/if}"
	>
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{@$contentTitle}{if !$contentTitleBadge|empty} {@$contentTitleBadge}{/if}</h1>
			{if !$contentDescription|empty}<p class="contentHeaderDescription">{@$contentDescription}</p>{/if}
			
			<ul class="inlineList contentHeaderMetaData">
				{event name='beforeMetaData'}
				
				<li itemprop="author" itemscope itemtype="http://schema.org/Person">
					<span class="icon icon16 fa-user"></span>
					
					{if $todo->userID}
						{user object=$todo->getUserProfile()}
					{else}
						<span>{$todo->username}</span>
					{/if}
				</li>
				
				<li>
					<span class="icon icon16 fa-clock-o"></span>
					{@$todo->creationDate|time}
					<meta itemprop="dateCreated" content="{@$todo->creationDate|date:'c'}">
					<meta itemprop="datePublished" content="{@$todo->creationDate|date:'c'}">
					<meta itemprop="operatingSystem" content="N/A">
				</li>
				
				<li class="jsMarkAsDone">
					{if $todo->isDone()}
						<span class="icon icon16 fa-check-square-o" data-tooltip="{lang}todolist.general.done{/lang}" aria-label="{lang}todolist.general.done{/lang}"></span>
						<span class="doneTitle">{lang}todolist.general.done{/lang}</span>
					{else}
						<span class="icon icon16 fa-square-o" data-tooltip="{lang}todolist.general.undone{/lang}" aria-label="{lang}todolist.general.undone{/lang}"></span>
						<span class="doneTitle">{lang}todolist.general.undone{/lang}</span>
					{/if}
				</li>
				
				{event name='afterMetaData'}
			</ul>
		</div>
		
		{hascontent}
			<nav class="contentHeaderNavigation">
				<ul>
					{content}
						{if !$contentHeaderNavigation|empty}{@$contentHeaderNavigation}{/if}
						
						{event name='contentHeaderNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</header>
{/capture}

{include file='header'}

{assign var=tabMenu value='0'}

{hascontent}
{assign var=tabMenu value='1'}

<div class="section tabMenuContainer">

	<nav class="tabMenu">
		<ul>
			<li><a href="{@$__wcf->getAnchor('generalTab')}">{lang}todolist.general.title{/lang}</a></li>
			
			{content}{event name='tabMenuTabs'}{/content}
		</ul>
	</nav>
{/hascontent}

	
	{* Tab 1 *}
	<div id="todoContent{if $tabMenu == 1} generalTab" class="tabMenuContent{/if}"
		{event name='todoContentAttributes'}
	>
		<div class="section">
			<div class="section">
				<p class="todoTitle">
					{$todo->getTitle()}
				</p>

				<p class="todoDescription">
					{$todo->description}
				</p>
			</div>

			{event name='beforeTodoButtons'}

			{hascontent}
				<div class="section">
					<ul class="todoButtons buttonGroup jsTodoInlineEditorContainer" data-todo-id="{@$todo->todoID}">
						{content}
							{if $todo->canEdit()}
								<li>
									<a href="{link application='todolist' controller='TodoEdit' id=$todo->todoID}{/link}" class="small button jsTodoInlineEditor" id="todoEditButton">
										<span class="icon icon16 fa-pencil"></span>
										<span>{lang}wcf.global.button.edit{/lang}</span>
									</a>
								</li>
							{/if}
							
							{if $__wcf->session->getPermission('user.todolist.canAddTodos')}
								<li>
									<a href="{link application='todolist' controller='TodoAdd'}{/link}" class="small button" id="todoAddButton">
										<span class="icon icon16 fa-plus"></span>
										<span>{lang}todolist.action.add{/lang}</span>
									</a>
								</li>
							{/if}

							{event name='todoButtons'}
						{/content}
					</ul>
				</div>	
			{/hascontent}
		</div>
	</div>
	{* End - Tab 1 *}

	{event name='tabMenuContents'}
{if $tabMenu == 1}</div>{/if}

{event name='beforeContentFooter'}

<footer class="contentFooter">
    {hascontent}
        <nav class="contentFooterNavigation">
            <ul>
                {content}{event name='contentFooterNavigation'}{/content}
            </ul>
        </nav>
    {/hascontent}
</footer>

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({	
			'todolist.action.markAsDone':					'{jslang}todolist.action.markAsDone{/jslang}',
			'todolist.action.markAsUndone':					'{jslang}todolist.action.markAsUndone{/jslang}',
			'todolist.action.confirmDelete':				'{jslang}todolist.action.confirmDelete{/jslang}',
			'todolist.general.done':						'{jslang}todolist.general.done{/jslang}',
			'todolist.general.undone':						'{jslang}todolist.general.undone{/jslang}'
		});
		
		var $updateHandler = new Todolist.Todo.UpdateHandler.Todo();
		
		new Todolist.Todo.MarkAsDone($updateHandler);
		
		var $inlineEditor = new Todolist.Todo.InlineEditor('.jsTodoInlineEditorContainer');
		$inlineEditor.setRedirectURL('{link application='todolist' controller='TodoList' encode=false}{/link}');
		$inlineEditor.setUpdateHandler($updateHandler);
		$inlineEditor.setPermissions({
			canDeleteTodo:		{if $todo->canDelete()}1{else}0{/if},
			canMarkAsDone:		{if $todo->canEdit()}1{else}0{/if}
		});
	});
</script>

{event name='additionalJavascript'}

{include file='footer'}
