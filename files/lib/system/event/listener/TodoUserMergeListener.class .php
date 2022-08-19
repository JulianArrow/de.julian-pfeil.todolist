<?php

namespace todolist\system\event\listener;

use wcf\system\event\listener\AbstractUserMergeListener;

/**
 * Updates todo information during user merging.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package WoltLabSuite\Core\System\Event\Listener
 */
class TodoUserMergeListener extends AbstractUserMergeListener
{
    /**
     * @inheritDoc
     */
    protected $databaseTables = [
        'todolist{WCF_N}_todo',
    ];
}
