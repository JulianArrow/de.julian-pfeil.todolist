/**
 * Deletes a given todo.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package     de.julian-pfeil.todolist
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax"], function (require, exports, tslib_1, Ajax) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Delete = void 0;
    Ajax = tslib_1.__importStar(Ajax);
    class Delete {
        todoIDs;
        successCallback;
        constructor(todoIDs, successCallback) {
            this.todoIDs = todoIDs;
            this.successCallback = successCallback;
        }
        delete() {
            Ajax.apiOnce({
                data: {
                    actionName: "delete",
                    className: "todolist\\data\\todo\\TodoAction",
                    objectIDs: this.todoIDs,
                },
                success: this.successCallback,
            });
        }
    }
    exports.Delete = Delete;
    exports.default = Delete;
});
