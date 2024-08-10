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
// Copyright (2002-2020) Author Dany De Bontridder <danydb@noalyss.eu>

/**
 * @class
 *
 */
var TagGroup = function ()
{
    this.callback = "ajax_misc.php";
    this.param_operation = "tag_set_group";
    this.list_tag = "ol_tag_group";
    this.div_tag_add = "d_tag_group_add";
    /**
     * Add a tag to group and redraw the div "div_tag_add"
     * @param {int} p_tag_group_id
     * @param {int} p_tag_id
     * @param {int} p_dossier
     */
    this.add_tag = function (p_tag_group_id, p_select, p_dossier) {
        waiting_box();
         // retrieve value from p_select
        var p_tag_id = $(p_select).value;
        new Ajax.Request(this.callback, {
            method: "get",
            parameters: {op: this.param_operation, gDossier: p_dossier, act: "add", tag_group: p_tag_group_id, tag: p_tag_id},
            onSuccess: function (req) {
                remove_waiting_box();
                // redraw div_tag_add
                $("d_tag_group_add").update(req.responseText);
            }
        });
    };
    /**
     * removing a tag from a group and redraw the div "div_tag_add"
     * @param {int} p_tag_group_id
     * @param {int} p_tag_id
     * @param {int} p_dossier
     */
    this.remove = function (p_jt_tag, p_dossier) {
        waiting_box();
       
        new Ajax.Request(this.callback, {
            method: "get",
            parameters: {op: this.param_operation, 
                gDossier: p_dossier, 
                act: "remove", 
                jt_tag: p_jt_tag
                },
            onSuccess: function (req) {
                remove_waiting_box();
                // redraw div_tag_add
                $("d_tag_group_add").update(req.responseText);
            }
        });
    };
};
