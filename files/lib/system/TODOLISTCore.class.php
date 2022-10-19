<?php

namespace todolist\system;

use todolist\page\TodoListPage;
use wcf\system\application\AbstractApplication;

/**
 * This is the core class for the todolist extending the wcf-own core class.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
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
