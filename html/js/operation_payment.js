/* 
 * Copyright (C) 2025 dany
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


///////////////////////////////////////////////////////////////////////
// Operation payement
// Selection on operation history : payment
var Operation_Payment = function (range_name, dossier_id, ac) {
    this.lastcheck = null;
    this.endcheck = null;
    this.range_name = range_name;
    this.dossier_id = dossier_id;
    this.ac = ac;
};

Operation_Payment.prototype.activate_checkbox_range = function () {
    let node_lstCheckBox = document.getElementsByClassName(this.range_name);
    var aCheckBox = Array.from(node_lstCheckBox)
    if (aCheckBox == undefined) {
        console.error("activate_checkbox_range_failed")
    }
    var here = this;
    aCheckBox.forEach(elt => elt.addEventListener('click', function (event) {
            here.checkbox_set_range(event, elt);
        }, false));
};

Operation_Payment.prototype.checkbox_set_range = function (event, elt)
{

    if (!event.shiftKey) {
        this.lastcheck = elt;
        return;
    }
    waiting_box();
    var aName = document.getElementsByClassName(this.range_name);

    var from = 0;
    var end = 0;
    for (var i = 0; i < aName.length; i++) {
        if (aName[i] == elt) {
            this.endcheck = aName[i];
            from = i;
        }
        if (aName[i] == this.lastcheck) {
            end = i;
        }
    }
    if (from > end) {
        let a = from;
        from = end;
        end = a;
    }
    var check = (aName[from].checked) ? true : false;
    for (x = from + 1; x < end; x++) {
        if (aName[x].parentNode.parentNode.visible()) {
            aName[x].checked=check;
            this.check_item(aName[x],true)
        }
    }
    remove_waiting_box();
};

Operation_Payment.prototype.check_item = function (dom_elt,flag_waiting_box)
{
    try
    {
        if ( ! flag_waiting_box ) waiting_box();
        var queryString = {op:'payment_status',operation_id:dom_elt.name,gDossier:this.dossier_id,ac:this.ac,state:dom_elt.checked};
        
        var action = new Ajax.Request(
                "ajax_misc.php",
                {
                    method: 'POST',
                    parameters: queryString,
                    onFailure: ajax_misc_failure,
                    onSuccess: function (req) {
                        if ( ! flag_waiting_box )remove_waiting_box();
                        if (req.responseText == 'NOCONX') {
                            reconnect();
                            return;
                        }
                        if ( req.responseText == 1) { dom_elt.checked=true}
                            else 
                            if ( req.responseText == 0) { dom_elt.checked=false}
                        else {
                            console.error(req.responseText)
                        }

                    }
                }
        );
    } catch (e)
    {
        alert_box(e.message);
    }

};
//Operation_Payment.prototype.check_all=function()
//{
//    var aName = document.getElementsByClassName(this.range_name);    
//    for (x = 0; x < aName.lenght; x++) {
//        if (aName[x].parentNode.parentNode.visible()) {
//            aName[x].checked=true;
//            this.check_item(aName[x]);
//        }
//    }
//    
//}
//Operation_Payment.prototype.invert_selection=function()
//{
//     var aName = document.getElementsByClassName(this.range_name);    
//    for (x = 0; x < aName.lenght; x++) {
//        if (aName[x].parentNode.parentNode.visible()) {
//            if ( aName[x].checked ) {
//                        aName[x].checked=false;
//            } else {
//                        aName[x].checked=true;
//            }
//            this.check_item(aName[x]);
//        }
//    }
//    
//}

