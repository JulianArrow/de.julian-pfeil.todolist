<?php

namespace todolist\system;

use todolist\page\TodoListPage;
use wcf\system\application\AbstractApplication;

/**
 * This is the core class for the todolist extending the wcf-own core class.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 */
class TODOLISTCore extends AbstractApplication
{
    /**
     * @inheritDoc
     */
    protected $primaryController = TodoListPage::class;
}
