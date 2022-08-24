/**
 * Handles mark-as-done buttons triggering user notifications.
 *
 * @author  Julian Pfeil <https://julian-pfeil.de>
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 */

 import * as Ajax from "WoltlabSuite/Core/Ajax";

 /**
  * Initializes notification trigger.
  */
 export function setup(): void {
   document.querySelectorAll(".jsMarkAsDone").forEach((button) => {
     button.addEventListener("click", (ev) => click(ev));
   });
 }
 
 /**
  * Sends a request to send notification.
  */
 function click(event: Event): void {
   const button = event.currentTarget as HTMLElement;
 
   Ajax.apiOnce({
     data: {
       actionName: "sendNotification",
       className: "todolist\\data\\todo\\TodoAction",
       objectIDs: [button.dataset.objectId!],
     },
   });
 }