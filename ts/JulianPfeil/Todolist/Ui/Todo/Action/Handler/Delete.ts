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

import * as Ajax from "WoltLabSuite/Core/Ajax";
import { CallbackSuccess } from "WoltLabSuite/Core/Ajax/Data";

export class Delete {
  private todoIDs: number[];
  private successCallback: CallbackSuccess;

  public constructor(todoIDs: number[], successCallback: CallbackSuccess, deleteMessage: string) {
    this.todoIDs = todoIDs;
    this.successCallback = successCallback;
  }

  public delete(): void {
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

export default Delete;