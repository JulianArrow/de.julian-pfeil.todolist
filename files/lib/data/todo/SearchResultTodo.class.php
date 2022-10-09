<?php

namespace todolist\data\todo;

use wcf\data\search\ISearchResultObject;
use wcf\system\search\SearchResultTextParser;

/**
 * Represents a todolist search result.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class SearchResultTodo extends Todo implements ISearchResultObject
{
    /**
     * @inheritDoc
     */
    public function getContainerLink()
    {
        return '';
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
    public function getObjectTypeName()
    {
        return 'de.julian-pfeil.todolist.todo';
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getDecoratedObject()->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function getTime()
    {
        return $this->time;
    }
}
