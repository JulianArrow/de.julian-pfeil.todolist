<?php
namespace todolist\system\user\notification\event;
use todolist\system\todo\TodoDataHandler;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;
use wcf\system\WCF;

/**
 * User notification event for todo comments.
 * 
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class TodoCommentUserNotificationEvent extends AbstractSharedUserNotificationEvent {
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
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		return [
				'message-id' => 'de.julian-pfeil.todolist.comment/'.$this->getUserNotificationObject()->commentID,
				'template' => 'email_notification_comment',
				'application' => 'wcf',
				'variables' => [
						'commentID' => $this->getUserNotificationObject()->commentID,
						'todo' => TodoDataHandler::getInstance()->getTodo($this->getUserNotificationObject()->objectID),
						'languageVariablePrefix' => 'todolist.comment.notification'
				]
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->getUserNotificationObject()->objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		$todo = TodoDataHandler::getInstance()->getTodo($this->getUserNotificationObject()->objectID);
		
		return LinkHandler::getInstance()->getLink('Todo', [
				'application' => 'todolist',
				'object' => $todo
		], '#comments/comment' . $this->getUserNotificationObject()->commentID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$todo = TodoDataHandler::getInstance()->getTodo($this->getUserNotificationObject()->objectID);
		
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable('todolist.comment.notification.message.stacked', [
					'author' => $this->author,
					'authors' => array_values($authors),
					'commentID' => $this->getUserNotificationObject()->commentID,
					'count' => $count,
					'todo' => $todo,
					'others' => $count - 1,
					'guestTimesTriggered' => $this->notification->guestTimesTriggered
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('todolist.comment.notification.message', [
				'todo' => $todo,
				'author' => $this->author,
				'commentID' => $this->getUserNotificationObject()->commentID
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('todolist.comment.notification.title.stacked', [
					'count' => $count,
					'timesTriggered' => $this->notification->timesTriggered
			]);
		}
	
		return $this->getLanguage()->get('todolist.comment.notification.title');
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		TodoDataHandler::getInstance()->cacheTodoID($this->getUserNotificationObject()->objectID);
	}
}
