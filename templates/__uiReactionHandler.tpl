{if MODULE_LIKE && $__wcf->getUser()->userID && $__wcf->getSession()->getPermission('user.like.canViewLike')}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Reaction/Handler'], function(UiReactionHandler) {
			new UiReactionHandler('de.julian-pfeil.todolist.likeableTodo', {
				// settings
				isSingleItem: true,
				
				// selectors
				buttonSelector: '#todoReactButton',
				containerSelector: '#generalTab',
				summaryListSelector: '.reactionSummaryList'
			});
		});
	</script>
{/if}