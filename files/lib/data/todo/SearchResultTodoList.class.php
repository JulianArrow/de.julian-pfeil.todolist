<?php
namespace todolist\data\todo;

/**
 * Represents a list of todolist search results.
 * 
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
class SearchResultTodoList extends TodoList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = SearchResultTodo::class;
}
