<?php

namespace todolist\system;

use wcf\system\application\AbstractApplication;
use todolist\page\TodoListPage;

/**
 * This is the core class for the todolist extending the wcf-own core class.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package Todolist/Core
 */
class TODOLISTCore extends AbstractApplication
{
    /**
     * @inheritDoc
     */
    protected $primaryController = TodoListPage::class;
}
