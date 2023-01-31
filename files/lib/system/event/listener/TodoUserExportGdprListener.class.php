<?php

namespace todolist\system\event\listener;

use wcf\acp\action\UserExportGdprAction;
use wcf\system\event\listener\AbstractEventListener;

/**
 * Adds the ip addresses stored with the todo during user data export.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.event.listener
 */
class TodoUserExportGdprListener extends AbstractEventListener
{
    protected function onExport(UserExportGdprAction $action): void
    {
        $action->ipAddresses['de.julian-pfeil.todolist'] = ['todolist' . WCF_N . '_todo'];
    }
}
