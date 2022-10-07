{if $mimeType === 'text/plain'}
{lang}todolist.action.notification.mail.plaintext{/lang}

{@$event->getUserNotificationObject()->getMailText($mimeType)} {* this line ends with a space *}
{else}
	{lang}todolist.action.notification.mail.html{/lang}
	{assign var='user' value=$event->getAuthor()}
	{assign var='todo' value=$event->getUserNotificationObject()}
	
	{if $notificationType == 'instant'}{assign var='avatarSize' value=48}
	{else}{assign var='avatarSize' value=32}{/if}
	{capture assign='todoContent'}
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td><a href="{link controller='User' object=$user isHtmlEmail=true}{/link}" title="{$user->username}">{@$user->getAvatar()->getImageTag($avatarSize)}</a></td>
			<td class="boxContent">
				<div class="containerHeadline">
					<h3>
						{if $user->userID}
							<a href="{link controller='User' object=$user isHtmlEmail=true}{/link}">{$user->username}</a>
						{else}
							{$user->username}
						{/if}
						&#xb7;
						<a href="{$todo->getLink()}"><small>{$todo->time|plainTime}</small></a>
					</h3>
				</div>
				<div>
					{@$todo->getMailText($mimeType)}
				</div>
			</td>
		</tr>
	</table>
	{/capture}
	{include file='email_paddingHelper' block=true class='box'|concat:$avatarSize content=$todoContent sandbox=true}
{/if}
