<?php
namespace todolist\system\comment\manager;

use todolist\data\todo\Todo;
use todolist\data\todo\TodoEditor;
use todolist\system\cache\runtime\TodoRuntimeCache;
use wcf\system\comment\manager\AbstractCommentManager;
use wcf\system\WCF;

/**
 * Comment manager implementation for todo.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package WoltLabSuite\Core\System\Comment\Manager
 */
class TodoCommentManager extends AbstractCommentManager
{
    /**
     * @inheritDoc
     */
    protected $permissionAdd = 'user.todolist.comments.canAddComments';

    /**
     * @inheritDoc
     */
    protected $permissionAddWithoutModeration = 'user.todolist.comments.canAddCommentsWithoutModeration';

    /**
     * @inheritDoc
     */
    protected $permissionCanModerate = 'mod.todolist.comments.canModerateComments';

    /**
     * @inheritDoc
     */
    protected $permissionDelete = 'user.todolist.comments.canDeleteComments';

    /**
     * @inheritDoc
     */
    protected $permissionEdit = 'user.todolist.comments.canEditComments';

    /**
     * @inheritDoc
     */
    protected $permissionModDelete = 'mod.todolist.comments.canDeleteComments';

    /**
     * @inheritDoc
     */
    protected $permissionModEdit = 'mod.todolist.comments.canEditComments';

    /**
     * @inheritDoc
     */
    public function getLink($objectTypeID, $objectID)
    {
        return TodoRuntimeCache::getInstance()->getObject($objectID)->getLink();
    }

    /**
     * @inheritDoc
     */
    public function isAccessible($objectID, $validateWritePermission = false)
    {
        return TodoRuntimeCache::getInstance()->getObject($objectID) !== null;
    }

    /**
     * @inheritDoc
     */
    public function getTitle($objectTypeID, $objectID, $isResponse = false)
    {
        if ($isResponse) {
            return WCF::getLanguage()->get('todolist.comment.response');
        }

        return WCF::getLanguage()->getDynamicVariable('todolist.comment.title');
    }

    /**
     * @inheritDoc
     */
    public function updateCounter($objectID, $value)
    {
        (new TodoEditor(new Todo($objectID)))->updateCounters(['comments' => $value]);
    }

    
	/**
	 * @inheritdoc
	 */
	public function prepare(array $likes) {
		if (!WCF::getSession()->getPermission('user.todolist.general.canSeeTodos')) {
			return;
		}
		
		$commentLikeObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.like.likeableObject', 'com.woltlab.wcf.comment');
		 
		$commentIDs = $responseIDs = [];
		foreach ($likes as $like) {
			if ($like->objectTypeID == $commentLikeObjectType->objectTypeID) {
				$commentIDs[] = $like->objectID;
			}
			else {
				$responseIDs[] = $like->objectID;
			}
		}
		
		// fetch response
		$userIDs = $responses = [];
		if (!empty($responseIDs)) {
			$responseList = new CommentResponseList();
			$responseList->setObjectIDs($responseIDs);
			$responseList->readObjects();
			$responses = $responseList->getObjects();
			
			foreach ($responses as $response) {
				$commentIDs[] = $response->commentID;
				if ($response->userID) {
					$userIDs[] = $response->userID;
				}
			}
		}
		
		// fetch comments
		$commentList = new CommentList();
		$commentList->setObjectIDs($commentIDs);
		$commentList->readObjects();
		$comments = $commentList->getObjects();
		
		// fetch users
		$users = [];
		$entryIDs = [];
		foreach ($comments as $comment) {
			$entryIDs[] = $comment->objectID;
			if ($comment->userID) {
				$userIDs[] = $comment->userID;
			}
		}
		if (!empty($userIDs)) {
			$users = UserProfileRuntimeCache::getInstance()->getObjects(array_unique($userIDs));
		}
		
		$entrys = [];
		if (!empty($entryIDs)) {
			$entryList = new ViewableEntryList();
			$entryList->setObjectIDs($entryIDs);
			$entryList->readObjects();
			$entrys = $entryList->getObjects();
		}
		
		// set message
		foreach ($likes as $like) {
			if ($like->objectTypeID == $commentLikeObjectType->objectTypeID) {
				// comment like
				if (isset($comments[$like->objectID])) {
					$comment = $comments[$like->objectID];
					
					if (isset($entrys[$comment->objectID]) && $entrys[$comment->objectID]->canRead()) {
						$like->setIsAccessible();
						
						// short output
						$text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.de.julian-pfeil.todolist.todoComment', [
								'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
								'comment' => $comment,
								'entry' => $entrys[$comment->objectID],
								'like' => $like
						]);
						$like->setTitle($text);
						
						// output
						$like->setDescription($comment->getExcerpt());
					}
				}
			}
			else {
				// response like
				if (isset($responses[$like->objectID])) {
					$response = $responses[$like->objectID];
					$comment = $comments[$response->commentID];
					
					if (isset($entrys[$comment->objectID]) && $entrys[$comment->objectID]->canRead()) {
						$like->setIsAccessible();
						
						// short output
						$text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.de.julian-pfeil.todolist.todoComment.response', [
								'responseAuthor' => $comment->userID ? $users[$response->userID] : null,
								'response' => $response,
								'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
								'entry' => $entrys[$comment->objectID],
								'like' => $like
						]);
						$like->setTitle($text);
						
						// output
						$like->setDescription($response->getExcerpt());
					}
				}
			}
		}
	}
}
