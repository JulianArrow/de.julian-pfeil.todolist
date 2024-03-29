<?php

/**
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 */

// define paths
\define('RELATIVE_TODOLIST_DIR', '../');
/*
 * include config
 * @noinspection PhpIncludeInspection
 */
require_once \dirname(__FILE__, 2) . '/app.config.inc.php';
/*
 * include wcf
 */
require_once RELATIVE_WCF_DIR . 'acp/global.php';
