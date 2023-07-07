<?php

use wcf\system\event\EventHandler;
use wcf\system\worker\event\RebuildWorkerCollecting;

return static function (): void {
    $eventHandler = EventHandler::getInstance();

    $eventHandler->register(RebuildWorkerCollecting::class, static function (RebuildWorkerCollecting $event) {
        $event->register(\todolist\system\worker\TodolistRebuildDataWorker::class, 0);
    });
};