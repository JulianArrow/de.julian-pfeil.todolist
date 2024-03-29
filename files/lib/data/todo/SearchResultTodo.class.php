<?php

namespace todolist\data\todo;

use wcf\data\search\ISearchResultObject;
use wcf\system\search\SearchResultTextParser;

/**
 * Represents a todolist search result.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package     de.julian-pfeil.todolist
 * @subpackage  data.todo
 */
class SearchResultTodo extends ViewableTodo implements ISearchResultObject
{
    /**
     * @inheritDoc
     */
    public function getFormattedMessage()
    {
        return SearchResultTextParser::getInstance()->parse($this->getDecoratedObject()->getSimplifiedFormattedMessage());
    }

    /**
     * @inheritDoc
     */
    public function getLink($query = '')
    {
        return $this->getDecoratedObject()->getLink();
    }

    /**
     * @inheritDoc
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @inheritDoc
     */
    public function getSubject()
    {
        return $this->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function getObjectTypeName()
    {
        return 'de.julian-pfeil.todolist.todo';
    }

    /**
     * @inheritDoc
     */
    public function getContainerTitle()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getContainerLink()
    {
        return '';
    }
}
