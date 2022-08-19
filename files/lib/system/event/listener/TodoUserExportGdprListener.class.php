<?php

namespace todolist\system\event\listener;

use wcf\system\event\listener\AbstractEventListener;
use wcf\acp\action\UserExportGdprAction;

/**
 * Adds the ip addresses stored with the todo during user data export.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package WoltLabSuite\Core\System\Event\Listener
 */
class TodoUserExportGdprListener extends AbstractEventListener
{
    protected function onExport(UserExportGdprAction $action): void
    {
        $action->ipAddresses['de.julian-pfeil.todolist'] = ['todolist' . WCF_N . '_todo'];
    }
}
