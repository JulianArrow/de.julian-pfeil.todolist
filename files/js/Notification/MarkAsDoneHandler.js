/**
 * Handles mark-as-done buttons triggering user notifications.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */
define(["require", "exports", "tslib", "WoltlabSuite/Core/Ajax"], function (require, exports, tslib_1, Ajax) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Ajax = tslib_1.__importStar(Ajax);
    /**
     * Initializes notification trigger.
     */
    function setup() {
        document.querySelectorAll(".jsMarkAsDone").forEach((button) => {
            button.addEventListener("click", (ev) => click(ev));
        });
    }
    exports.setup = setup;
    /**
     * Sends a request to send notification.
     */
    function click(event) {
        const button = event.currentTarget;
        Ajax.apiOnce({
            data: {
                actionName: "sendNotification",
                className: "todolist\\data\\todo\\TodoAction",
                objectIDs: [button.dataset.objectId],
            },
        });
    }
});
