{capture assign='contentTitle'}{lang}todolist.general.list{/lang}{/capture}

{assign var='additionalLinkParameters' value=''}
{if $categoryID}{capture append='additionalLinkParameters'}&categoryID={$categoryID}{/capture}{/if}
{if $isDone}{capture append='additionalLinkParameters'}&isDone={$isDone}{/capture}{/if}
{if $sortField}{capture append='additionalLinkParameters'}&sortField={$sortField}{/capture}{/if}
{if $sortOrder}{capture append='additionalLinkParameters'}&sortOrder={$sortOrder}{/capture}{/if}
{if $labelIDs|count}{capture append='additionalLinkParameters'}{foreach from=$labelIDs key=labelGroupID item=labelID}&labelIDs[{@$labelGroupID}]={@$labelID}{/foreach}{/capture}{/if}

{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link application='todolist' controller='TodoList'}pageNo={@$pageNo+1}{@$additionalLinkParameters}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link application='todolist' controller='TodoList'}{if $pageNo > 2}pageNo={@$pageNo-1}{@$additionalLinkParameters}{/if}{/link}">
	{/if}
	<link rel="canonical" href="{link application='todolist' controller='TodoList'}{if $pageNo > 1}pageNo={@$pageNo}{@$additionalLinkParameters}{/if}{/link}">
{/capture}

{capture assign='contentHeaderNavigation'}
	{include file='todoAddButton' application='todolist'}
{/capture}

{capture assign='sidebarRight'}
	{include file='todoListSidebar' application='todolist'}
{/capture}

{include file='header'}

{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign=pagesLinks application='todolist' controller='TodoList' link="pageNo=%d$additionalLinkParameters"}
		{/content}
	</div>
{/hascontent}


<div class="section">
	{include file='todoListStructure' application='todolist'}
</div>

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
					{include file='todoAddButton' application='todolist'}

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

		{if !$labelGroups|empty}
			WCF.Language.addObject({
				'wcf.label.none': '{jslang}wcf.label.none{/jslang}',
				'wcf.label.withoutSelection': '{jslang}wcf.label.withoutSelection{/jslang}'
			});
			
			new WCF.Label.Chooser({ {implode from=$labelIDs key=groupID item=labelID}{@$groupID}: {@$labelID}{/implode} }, '#todolistLabelForm', undefined, true);
		{/if}
	});
</script>

{event name='additionalJavascript'}

{include file='footer'}
