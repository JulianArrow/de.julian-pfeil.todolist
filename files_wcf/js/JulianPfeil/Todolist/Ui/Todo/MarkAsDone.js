/**
 * Marks a given todo as done or undone.
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://julian-pfeil.de/r/plugins
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by-nd> <https://creativecommons.org/licenses/by-nd/4.0/legalcode>
 *
 * @package     de.julian-pfeil.todolist
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ajax/Backend"], function (require, exports, tslib_1, Language, Backend_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.MarkAsDone = void 0;
    Language = tslib_1.__importStar(Language);
    class MarkAsDone {
        /**
         * Basic constructor
         */
        constructor() {
            const buttons = document.querySelectorAll(".jsMarkAsDone");
            if (buttons) {
                buttons.forEach((button) => {
                    button.addEventListener("click", (ev) => {
                        ev.preventDefault();
                        this.click(button);
                    });
                });
            }
        }
        /**
         * Action button was clicked
         */
        click(button) {
            const isDone = parseInt(button.dataset.isDone);
            let markAsDone = false;
            if (!isDone) {
                markAsDone = true;
            }
            const data = {
                markAsDone: markAsDone,
            };
            void this.fireRequest(button, data);
        }
        /**
         * Fire action request
         * @param button
         * @param data
         */
        async fireRequest(button, data) {
            const response = await (0, Backend_1.prepareRequest)(button.dataset.endpoint).post(data).fetchAsResponse();
            const todosButtons = document.querySelectorAll(".jsMarkAsDone[data-object-id=\"" + button.dataset.objectId + "\"]");
            if (response?.ok) {
                if (todosButtons) {
                    if (data.markAsDone == true) {
                        todosButtons.forEach((todoButton) => {
                            todoButton.setAttribute("data-is-done", "1");
                            if (todoButton.tagName == 'a') {
                                todoButton.querySelector("span").innerHTML = Language.getPhrase('todolist.action.markAsUndone');
                            }
                            else {
                                todoButton.querySelectorAll("span:not(.doneTitle)").forEach((notDoneTitle) => {
                                    notDoneTitle.setAttribute("aria-label", Language.getPhrase('todolist.general.isDone'));
                                    notDoneTitle.setAttribute("data-tooltip", Language.getPhrase('todolist.general.isDone'));
                                });
                            }
                            todoButton.querySelectorAll("span.doneTitle").forEach((doneTitle) => {
                                doneTitle.innerHTML = Language.getPhrase('todolist.general.isDone');
                            });
                            todoButton.querySelectorAll("fa-icon").forEach((icon) => {
                                icon.setIcon("check-square");
                            });
                        });
                    }
                    else {
                        todosButtons.forEach((todoButton) => {
                            todoButton.setAttribute("data-is-done", "0");
                            if (todoButton.tagName == 'a') {
                                todoButton.querySelector("span").innerHTML = Language.getPhrase('todolist.action.markAsDone');
                            }
                            else {
                                todoButton.querySelectorAll("span:not(.doneTitle)").forEach((notDoneTitle) => {
                                    notDoneTitle.setAttribute("aria-label", Language.getPhrase('todolist.general.isUndone'));
                                    notDoneTitle.setAttribute("data-tooltip", Language.getPhrase('todolist.general.isUndone'));
                                });
                            }
                            todoButton.querySelectorAll("span.doneTitle").forEach((doneTitle) => {
                                doneTitle.innerHTML = Language.getPhrase('todolist.general.isUndone');
                            });
                            todoButton.querySelectorAll("fa-icon").forEach((icon) => {
                                icon.setIcon("square");
                            });
                        });
                    }
                }
            }
        }
    }
    exports.MarkAsDone = MarkAsDone;
    exports.default = MarkAsDone;
});
