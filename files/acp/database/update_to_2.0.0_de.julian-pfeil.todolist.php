<?php

/**
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 */

use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('todolist1_todo')
        ->columns([
            DefaultFalseBooleanDatabaseTableColumn::create('done')
                ->renameTo('isDone'),
            NotNullInt10DatabaseTableColumn::create('creationDate')
                ->renameTo('time'),
        ]),
];
