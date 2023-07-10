<?php

namespace todolist\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use todolist\data\todo\Todo;
use todolist\system\todo\command\MarkAsDone;
use todolist\system\user\notification\object\TodoUserNotificationObject;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\WCF;

/**
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package     de.julian-pfeil.todolist
 * @subpackage  action
 */
final class TodoMarkAsDoneAction implements RequestHandlerInterface
{
    private const QUERY_PARAMETERS = <<<'EOT'
        array {
            id: positive-int
        }
        EOT;

    private const BODY_PARAMETERS = <<<'EOT'
        array {
            markAsDone: boolean
        }
        EOT;

    /**
     * @inheritDoc
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws IllegalLinkException
     * @throws PermissionDeniedException
     * @throws SystemException
     * @throws MappingError
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'GET') {
            return new TextResponse('Unsupported', 400);
        } elseif ($request->getMethod() !== 'POST') {
            throw new \LogicException('Unreachable');
        }

        // read parameters
        $queryParameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            self::QUERY_PARAMETERS,
        );

        $bodyParameters = Helper::mapRequestBody(
            $request->getParsedBody(),
            self::BODY_PARAMETERS,
        );

        // validate
        $todo = new Todo($queryParameters['id']);
        if (!$todo->todoID) {
            throw new IllegalLinkException();
        }

        if (!$todo->canEdit()) {
            throw new PermissionDeniedException();
        }

        // command
        $command = new MarkAsDone($todo, $bodyParameters['markAsDone']);
        $command();
        
        // author notification when edited
        if (WCF::getUser()->userID != $todo->userID) {
            UserNotificationHandler::getInstance()->fireEvent(
                'edit', // event name
                'de.julian-pfeil.todolist.todo', // event object type name
                new TodoUserNotificationObject(new Todo($todo->todoID)),
                [$todo->userID] //recipient
            );
        }

        // watched objects
        UserObjectWatchHandler::getInstance()->updateObject(
            'de.julian-pfeil.todolist.todo',
            $todo->todoID,
            'todo',
            'de.julian-pfeil.todolist.todo',
            new TodoUserNotificationObject(new Todo($todo->todoID))
        );

        return new EmptyResponse();
    }
}
