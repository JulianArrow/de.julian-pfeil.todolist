<?php

namespace todolist\system\label\object;

use todolist\system\cache\builder\TodoCategoryLabelCacheBuilder;

use wcf\system\label\LabelHandler;

/**
 * Label handler implementation for todos.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class TodoLabelObjectHandler extends AbstractLabelObjectHandler
{
    /**
     * @inheritdoc
     */
    protected $objectType = 'de.julian-pfeil.todolist.todo';

    /**
     * Sets the label groups available for the categories with the given ids.
     */
    public function setCategoryIDs($categoryIDs)
    {
        $groupedGroupIDs = TodoCategoryLabelCacheBuilder::getInstance()->getData();

        $groupIDs = [];
        foreach ($groupedGroupIDs as $categoryID => $__groupIDs) {
            if (\in_array($categoryID, $categoryIDs)) {
                $groupIDs = \array_merge($groupIDs, $__groupIDs);
            }
        }

        $this->labelGroups = [];
        if (!empty($groupIDs)) {
            $this->labelGroups = LabelHandler::getInstance()->getLabelGroups(\array_unique($groupIDs));
        }
    }
}
