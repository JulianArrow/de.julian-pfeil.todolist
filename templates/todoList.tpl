{capture assign='contentTitle'}{lang}todolist.general.list{/lang} <span class="badge">{#$items}</span>{/capture}

{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link application='todolist' controller='TodoList'}pageNo={@$pageNo+1}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link application='todolist' controller='TodoList'}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
	{/if}
	<link rel="canonical" href="{link application='todolist' controller='TodoList'}{if $pageNo > 1}pageNo={@$pageNo}{/if}{/link}">
{/capture}

{if $__wcf->session->getPermission('user.todolist.canAddTodos')}
	{capture assign='contentHeaderNavigation'}
		<li>
			<a href="{link application='todolist' controller='TodoAdd'}{/link}" class="button" id="todoAddButton">
				<span class="icon icon16 fa-plus"></span>
				<span>{lang}todolist.action.add{/lang}</span>
			</a>
		</li>
	{/capture}
{/if}

{if $items}
	{capture assign='sidebarRight'}
		<section class="box">
			<form method="post" action="{link application='todolist' controller='TodoList'}{/link}">
				<h2 class="boxTitle">{lang}wcf.global.sorting{/lang}</h2>
				
				<div class="boxContent">
					<dl>
						<dt></dt>
						<dd>
							<select id="sortField" name="sortField">
								<option value="todoName"{if $sortField == 'todoName'} selected{/if}>{lang}todolist.column.todoName{/lang}</option>
								<option value="creationDate"{if $sortField == 'creationDate'} selected{/if}>{lang}todolist.column.creationDate{/lang}</option>
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
					</div>
				</div>
			</form>
		</section>
	{/capture}
{/if}

{include file='header'}

{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign=pagesLinks application='todolist' controller='TodoList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&done=$done"}
		{/content}
	</div>
{/hascontent}


{if $items}
	<div class="section">

		<nav class="tabMenu">
			<ul>
				<li {if $done == ''}class ="active"{/if}><a href="{link application='todolist' controller='TodoList'}sortField={$sortField}&sortOrder={$sortOrder}{if $pageNo > 1}&pageNo={@$pageNo}{/if}{/link}">{lang}todolist.general.list{/lang}</a></li>
				<li {if $done == '0'}class ="active"{/if}><a href="{link application='todolist' controller='TodoList'}sortField={$sortField}&sortOrder={$sortOrder}{if $pageNo > 1}&pageNo={@$pageNo}{/if}&done=0{/link}">{lang}todolist.general.undone{/lang}</a></li>
				<li {if $done == '1'}class ="active"{/if}><a href="{link application='todolist' controller='TodoList'}sortField={$sortField}&sortOrder={$sortOrder}{if $pageNo > 1}&pageNo={@$pageNo}{/if}&done=1{/link}">{lang}todolist.general.done{/lang}</a></li>
				
				{event name='tabMenuTabs'}
			</ul>
		</nav>
		
		<div class="tabMenuContent">
			<div class="section sectionContainerList">
				<ul class="commentList containerList todoList jsObjectActionContainer jsReloadPageWhenEmpty" {*
					*}data-object-action-class-name="todolist\data\todo\TodoAction"{*
				*}>
					{if $items}
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
														<span class="icon icon16 fa-clock-o"></span>
														{@$todo->creationDate|time}
													</small>
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
					{/if}
				</ul>
			</div>
		</div>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}
	
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}
					{if $__wcf->session->getPermission('user.todolist.canAddTodos')}
						<li>
							<a href="{link application='todolist' controller='TodoAdd'}{/link}" class="button" id="todoAddButton">
								<span class="icon icon16 fa-plus"></span>
								<span>{lang}todolist.action.add{/lang}</span>
							</a>
						</li>
					{/if}
					{event name='contentFooterNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({	
			'todolist.general.done':						'{jslang}todolist.general.done{/jslang}',
			'todolist.general.undone':						'{jslang}todolist.general.undone{/jslang}'
		});
		var $updateHandler = new Todolist.Todo.UpdateHandler.Todolist();
		
		new Todolist.Todo.MarkAsDone($updateHandler);
	});
</script>

{event name='additionalJavascript'}

{include file='footer'}