<?php

namespace todolist\system\event\listener;

use wcf\system\event\listener\AbstractEventListener;
use wcf\system\cronjob\PruneIpAddressesCronjob;

/**
 * Prunes old ip addresses.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.event.listener
 */
class TodoPruneIpAddressesCronjobListener extends AbstractEventListener
{
    protected function onExecute(PruneIpAddressesCronjob $cronjob): void
    {
        $cronjob->columns['todolist' . WCF_N . '_todo']['ipAddress'] = 'time';
    }
}
