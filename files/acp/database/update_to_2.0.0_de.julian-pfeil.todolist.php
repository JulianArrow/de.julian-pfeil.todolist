<?php

use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;

return [
    PartialDatabaseTable::create('todolist1_todo')
        ->columns([
            DefaultFalseBooleanDatabaseTableColumn::create('done')
                ->renameTo('isDone'),
            NotNullInt10DatabaseTableColumn::create('creationDate')
                ->renameTo('time'),
        ]),
];
