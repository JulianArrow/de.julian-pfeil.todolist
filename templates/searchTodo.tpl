<dl>
	<dt><label for="todoCategoryID">{lang}todolist.search.categories{/lang}</label></dt>
	<dd>
		<select name="todoCategoryID" id="todoCategoryID">
			<option value="">{lang}wcf.global.language.noSelection{/lang}</option>
			{foreach from=$todoCategoryList item=category}
				<option value="{@$category->categoryID}">{$category->getTitle()}</option>
			{/foreach}
		</select>
	</dd>
</dl>

{event name='fields'}
