<?php

/**
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 */

use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;
use wcf\system\database\table\column\DefaultTrueBooleanDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\SmallintDatabaseTableColumn;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('todolist1_todo')
        ->columns([
            DefaultFalseBooleanDatabaseTableColumn::create('done')
                ->renameTo('isDone'),
            NotNullInt10DatabaseTableColumn::create('creationDate')
                ->renameTo('time'),
            SmallintDatabaseTableColumn::create('comments')
                ->drop(),
            DefaultTrueBooleanDatabaseTableColumn::create('enableComments')
                ->drop(),
        ]),
];
