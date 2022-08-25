<?php

use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\ObjectIdDatabaseTableColumn;
use wcf\system\database\table\column\TextDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;
use wcf\system\database\table\column\SmallintDatabaseTableColumn;
use wcf\system\database\table\column\DefaultTrueBooleanDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTablePrimaryIndex;

return [
    DatabaseTable::create('todolist1_todo')
        ->columns([
            ObjectIdDatabaseTableColumn::create('todoID'),
            NotNullVarchar255DatabaseTableColumn::create('todoName'),
            TextDatabaseTableColumn::create('description'),
            NotNullInt10DatabaseTableColumn::create('creationDate'),
			IntDatabaseTableColumn::create('userID')
                ->length(10),
            NotNullVarchar255DatabaseTableColumn::create('username'),
            VarcharDatabaseTableColumn::create('ipAddress')
                ->length(39)
                ->notNull(true)
                ->defaultValue(''),
            DefaultFalseBooleanDatabaseTableColumn::create('done'),
            SmallintDatabaseTableColumn::create('comments')
                ->length(5)
                ->notNull()
                ->defaultValue(0),
            DefaultTrueBooleanDatabaseTableColumn::create('enableComments'),
            SmallintDatabaseTableColumn::create('cumulativeLikes')
                ->length(5)
                ->notNull()
                ->defaultValue(0),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['todoID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['userID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('SET NULL'),
        ]),
];
