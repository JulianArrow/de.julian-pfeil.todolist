<?php

use wcf\event\acp\menu\item\ItemCollecting;
use wcf\system\event\EventHandler;
use wcf\system\menu\acp\AcpMenuItem;
use wcf\event\worker\RebuildWorkerCollecting;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;
use todolist\acp\page\CategoryListPage;
use todolist\acp\form\CategoryAddForm;
use wcf\system\WCF;

return static function (): void {
    $eventHandler = EventHandler::getInstance();

    $eventHandler->register(RebuildWorkerCollecting::class, static function (RebuildWorkerCollecting $event) {
        $event->register(\todolist\system\worker\TodolistRebuildDataWorker::class, 0);
    });

    $eventHandler->register(ItemCollecting::class, static function (ItemCollecting $event) {
        $event->register(new AcpMenuItem(
            "todolist.acp.menu.link",
            false,
            'wcf.acp.menu.link.application'
        ));

        if (WCF::getSession()->getPermission('admin.todolist.general.canManageCategory')) {
            $event->register(new AcpMenuItem(
                "todolist.acp.menu.link.todo.category",
                false,
                'todolist.acp.menu.link',
                LinkHandler::getInstance()->getControllerLink(CategoryListPage::class),
            ));

            $event->register(new AcpMenuItem(
                "todolist.acp.menu.link.todo.category.add",
                false,
                'todolist.acp.menu.link.todo.category',
                LinkHandler::getInstance()->getControllerLink(CategoryAddForm::class),
                FontAwesomeIcon::fromString('plus;false')
            ));
        }
    });
};