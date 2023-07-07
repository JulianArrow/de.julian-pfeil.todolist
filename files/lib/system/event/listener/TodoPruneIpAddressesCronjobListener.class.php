<?php

namespace todolist\system\event\listener;

use wcf\system\cronjob\PruneIpAddressesCronjob;
use wcf\system\event\listener\AbstractEventListener;

/**
 * Prunes old ip addresses.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package     de.julian-pfeil.todolist
 * @subpackage  system.event.listener
 */
class TodoPruneIpAddressesCronjobListener extends AbstractEventListener
{
    protected function onExecute(PruneIpAddressesCronjob $cronjob): void
    {
        $cronjob->columns['todolist' . WCF_N . '_todo']['ipAddress'] = 'time';
    }
}
