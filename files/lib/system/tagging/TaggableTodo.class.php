<?php

namespace todolist\system\tagging;

use todolist\data\todo\TaggedTodoList;
use wcf\system\tagging\AbstractCombinedTaggable;

/**
 * Implementation of ITaggable for tagging of todos.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class TaggableTodo extends AbstractCombinedTaggable
{
    /**
     * @inheritDoc
     */
    public function getObjectListFor(array $tags)
    {
        return new TaggedTodoList($tags);
    }

    /**
     * @inheritDoc
     */
    public function getTemplateName()
    {
        return 'taggedTodoList';
    }
}
