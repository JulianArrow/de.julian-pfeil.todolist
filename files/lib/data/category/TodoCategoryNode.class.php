<?php

namespace todolist\data\category;

use wcf\data\category\CategoryNode;

/**
 * Class TodoCategoryNode
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @method        TodoCategory        getDecoratedObject()
 * @mixin        TodoCategory
 */
class TodoCategoryNode extends CategoryNode
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = TodoCategory::class;
}