<div class="section">
	{foreach from=$objects item=$todo}
		<article class="message messageReduced">
			<section class="messageContent">
				<header class="messageHeader">
					<div class="box32 messageHeaderWrapper">
						{user object=$todo->getUserProfile() type='avatar32'}
						
						<div class="messageHeaderBox">
							<h2 class="messageTitle">
								{anchor object=$todo}
							</h2>
							
							<ul class="messageHeaderMetaData">
								<li>{user object=$todo->getUserProfile() class='username'}</li>
								<li><span class="messagePublicationTime">{@$todo->time|time}</span></li>
							</ul>
						</div>
					</div>
				</header>
				
				<div class="messageBody">
					<div class="messageText">
						{@$todo->getFormattedMessage()}
					</div>
				</div>
			</section>
		</article>
	{/foreach}
</div>
