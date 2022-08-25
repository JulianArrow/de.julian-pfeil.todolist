<?php
namespace todolist\system\user\notification\event;
use todolist\system\todo\TodoDataHandler;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;
use wcf\system\user\notification\event\TReactionUserNotificationEvent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * User notification event for todo comment likes.
 * 
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class TodoCommentLikeUserNotificationEvent extends AbstractSharedUserNotificationEvent {
	use TReactionUserNotificationEvent;
	
	/**
	 * @inheritDoc
	 */
	protected $stackable = true;
	
	/**
	 * @inheritDoc
	 */
	public function checkAccess() {
		return WCF::getSession()->getPermission('user.todolist.general.canSeeTodos');
	}
	
	/**
	 * Returns the liked comment's id.
	 */
	protected function getCommentID() {
		return $this->getUserNotificationObject()->objectID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') { /* not supported */ }
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->getCommentID());
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		$todo = TodoDataHandler::getInstance()->getTodo($this->additionalData['objectID']);
		
		return LinkHandler::getInstance()->getLink('Todo', [
				'application' => 'todolist',
				'object' => $todo
		], '#comments/comment' . $this->getCommentID());
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$todo = TodoDataHandler::getInstance()->getTodo($this->additionalData['objectID']);
		$authors = array_values($this->getAuthors());
		$count = count($authors);
		
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('todolist.comment.like.notification.message.stacked', [
					'author' => $this->author,
					'authors' => $authors,
					'commentID' => $this->getCommentID(),
					'count' => $count,
					'others' => $count - 1,
					'todo' => $todo,
					'reactions' => $this->getReactionsForAuthors()
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('todolist.comment.like.notification.message', [
				'author' => $this->author,
				'commentID' => $this->getCommentID(),
				'todo' => $todo,
				'reactions' => $this->getReactionsForAuthors()
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('todolist.comment.like.notification.title.stacked', [
					'count' => $count,
					'timesTriggered' => $this->notification->timesTriggered
			]);
		}
	
		return $this->getLanguage()->get('todolist.comment.like.notification.title');
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		TodoDataHandler::getInstance()->cacheTodoID($this->additionalData['objectID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function supportsEmailNotification() {
		return false;
	}
}
