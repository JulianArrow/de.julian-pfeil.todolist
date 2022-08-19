<?php

namespace todolist\system\event\listener;

use wcf\system\event\listener\AbstractUserActionRenameListener;

/**
 * Updates todo information during user renaming.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package WoltLabSuite\Core\System\Event\Listener
 */
class TodoUserActionRenameListener extends AbstractUserActionRenameListener
{
    /**
     * @inheritDoc
     */
    protected $databaseTables = [
        'todolist{WCF_N}_todo',
    ];
}
