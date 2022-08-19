<?php
/**
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 * @package Todolist/Core
 */
require_once './global.php';
wcf\system\request\RequestHandler::getInstance()->handle('todolist');