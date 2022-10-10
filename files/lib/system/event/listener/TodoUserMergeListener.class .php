<?php

namespace todolist\system\event\listener;

use wcf\system\event\listener\AbstractUserMergeListener;

/**
 * Updates todo information during user merging.
 *
 * @author     Julian Pfeil <https://julian-pfeil.de>
 * @link    https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.event.listener
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
