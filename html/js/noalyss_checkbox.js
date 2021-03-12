/* 
 * Copyright (C) 2020 Dany De Bontridder <dany@alchimerys.be>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
/**
 * 
 * @param {event} evt event 
 * @param {object} elt checkbox elt
 * @param {string} p_name name of the checkbox object
 
 * @returns {undefined}
 */

var lastcheck = null;
var endcheck = null;

/**
 * Check or uncheck checkbox if shift key is pressed and between the last checked elemet and the current one
 * @param {type} evt event
 * @param {type} elt Dom element 
 * @param {type} p_name name of range
 */
function checkbox_set_range(evt, elt, p_name) {
    if (!evt.shiftKey) {
        lastcheck = elt;
        return;
    }
    var aName = document.getElementsByClassName(p_name);

    var from = 0;
    var end = 0;
    for (var i = 0; i < aName.length; i++) {
        if (aName[i] == elt) {
            endcheck = aName[i];
            from = i;
        }
        if (aName[i] == lastcheck) {
            end = i;
        }
    }
    if (from > end) {
        let a = from;
        from = end;
        end = a;
    }
    var check = (aName[from].checked) ? true : false;
    for (x = from; x <= end; x++) {
        aName[x].checked = check;
    }
}


/**
 * For each checkbox , add an event on click
 * @param {string} p_range_name name of the checkbox object
 * @returns {undefined}
 */
function activate_checkbox_range(p_range_name) {
    let node_lstCheckBox = document.getElementsByClassName(p_range_name);
    var aCheckBox=Array.from(node_lstCheckBox)
    if (aCheckBox == undefined) {
        console.error("activate_checkbox_range_failed")
    }

    aCheckBox.forEach(elt => elt.addEventListener ('click',function (event) {
            checkbox_set_range(event, elt, p_range_name);
        },false));
}
;


