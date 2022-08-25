<?php

use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\database\table\column\SmallintDatabaseTableColumn;
use wcf\system\database\table\column\DefaultTrueBooleanDatabaseTableColumn;

return [
    PartialDatabaseTable::create('todolist1_todo')
        ->columns([
            SmallintDatabaseTableColumn::create('comments')
                ->length(5)
                ->notNull()
                ->defaultValue(0),
            DefaultTrueBooleanDatabaseTableColumn::create('enableComments'),
            SmallintDatabaseTableColumn::create('cumulativeLikes')
                ->length(5)
                ->notNull()
                ->defaultValue(0),
        ]),
];
