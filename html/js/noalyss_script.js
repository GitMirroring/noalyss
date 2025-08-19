/*
 *   This file is part of NOALYSS.
 *
 *   NOALYSS is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   NOALYSS is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with NOALYSS; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
/* $Revision$ */

// Copyright Author Dany De Bontridder danydb@aevalys.eu

/**
 *
 * javascript script, always added to every page
 *
 */
var ask_reload = 0;
// tag_choose Element  which contains all the selected tags 
var tag_choose = '';
var aDraggableElement = new Array();
// Layer for z-index , see function get_next_layer , must be used in PHP and JS
var layer=0;
// document.viewport depends of prototype.js
var viewport = document.viewport.getDimensions(); // Gets the viewport as an object literal
var width = viewport.width; // Usable window width
var height = viewport.height;

/**
 * return undefined if nothing is found , otherwise return the DOM elemnt, try to find an DOM Element inside p_element
 * @param {type} p_name_dom
 * @param {type} name_child
 * @returns {undefined}
 */
function in_child(p_element, name_child) {
    var element = p_element
    if (typeof p_element !== "object") {
        element = document.getElementById(p_element);

    }
    if (!element) return undefined;
    for (var e = 0; e < element.childElementCount; e++) {
        if (element.childNodes[e].id == name_child) {
            return element.childNodes[e];
        }
    }
}

/**
 * callback function when we just need to update a hidden div with an info
 * message
 * @see removeOperation , reverseOperation
 */
function infodiv(req, json) {
    try {
        remove_waiting_box();
        var answer = req.responseXML;
        var a = answer.getElementsByTagName('ctl');
        var html = answer.getElementsByTagName('code');
        if (a.length === 0) {
            var rec = req.responseText;
            alert_box('erreur :' + rec);
        }
        var name_ctl = a[0].firstChild.nodeValue;
        var code_html = getNodeText(html[0]);

        code_html = unescape_xml(code_html);
        id$(name_ctl + "info").innerHTML = code_html;
    } catch (e) {
        alert_box("success_box" + e.message);
    }
    try {
        code_html.evalScripts();
    } catch (e) {
        alert_box(content[53] + "\n" + e.message);
    }

}

/**
 * delete a row from a table (tb) the input button send the this
 * as second parameter
 */
function deleteRow(tb, obj) {
    smoke.confirm(content[50], function (e) {
        if (e) {
            var td = obj.parentNode;
            var tr = td.parentNode;
            var lidx = tr.rowIndex;
            id$(tb).deleteRow(lidx);

        } else {
            return;
        }
    });
}

function deleteRowRec(tb, obj) {
    var tr = obj;
    var lidx = tr.rowIndex;
    id$(tb).deleteRow(lidx);
}

/*!\brief remove trailing and heading space
 * \param the string to modify
 * \return string without heading and trailing space
 */
function trim(s) {
    return s.replace(/^\s+/, '').replace(/\s+$/, '');
}

/**
 *  retrieve an element thanks its ID
 * @param ID is a string
 * @return the found object of undefined if not found
 */
function id$(ID) {
    if (ID instanceof Object ) return ID;
    if (document.getElementById(ID)) {
            return document.getElementById(ID);
    } else if (document.all) {
        return document.all[ID];
    } else {
        document.debug_noalyss&&console.error(`id$ ${ID}`)
        return undefined;
    }
}

/**
 * this function is deprecated and replaced by id$
 * @deprecated
 * @param ID
 * @returns {*}
 */
function g(ID) {
    document.debug_noalyss&& console.warn(`g(${ID} is deprecated, use id$`);
    return id$(ID);
}
function get_next_layer(){
    return layer++;
}
/**
 * enable the type of periode
 */
function enable_type_periode() {
    if (document.getElementById("type_periode").options[id$("type_periode").selectedIndex].value == 0) {
        id$('from_periode').enable();
        id$('to_periode').enable();
        id$('from_date').disable();
        id$('to_date').disable();
        id$('p_step').enable();
    } else {
        id$('from_periode').disable();
        id$('to_periode').disable();
        id$('from_date').enable();
        id$('to_date').enable();
        id$('p_step').disable();
    }
}

/**
 * will reload the window but it is dangerous if we have submitted
 * a form with POST
 */
function refresh_window() {
    window.location.reload();
}

/**
 *@fn encodeJSON(obj)
 * we receive a json object as parameter and the function returns the string
 *       with the format variable=value&var2=val2...
 */
function encodeJSON(obj) {
    if (typeof obj != 'object') {
        alert_box('encodeParameter  obj n\'est pas  un objet');
    }
    try {
        var str = '';
        var e = 0;
        for (var i in obj) {
            if (e !== 0) {
                str += '&';
            } else {
                e = 1;
            }
            str += i;
            str += '=' + encodeURI(obj[i]);
        }
        return str;
    } catch (e) {
        alert_box('encodeParameter ' + e.message);
        return "";
    }
}

function hide(p_param) {
    id$(p_param).style.display = 'none';
}

function show(p_param) {
    id$(p_param).style.display = 'block';
}

/**
 * set the focus on the selected field
 *@param Field id of  the control
 *@param selectIt : the value selected in case of Field is a object select, numeric
 */
function SetFocus(Field, SelectIt) {
    var elem = id$(Field);
    if (elem) {
        elem.focus();
    }
    return true;
}

/**
 *  set a DOM id with a value in the parent window (the caller),
 @param p_ctl is the name of the control
 @param p_value is the value to set in
 @param p_add if we don't replace the current value but we add something
 */
function set_inparent(p_ctl, p_value, p_add) {
    self.opener.set_value(p_ctl, p_value, p_add);
}

/**
 *  set a DOM id with a value, it will consider if it the attribute
 value or innerHTML has be used
 @param p_ctl is the name of the control
 @param p_value is the value to set in
 @param p_add if we don't replace the current value but we add something
 */
function set_value(p_ctl, p_value, p_add) {
    if (document.getElementById(p_ctl)) {
        var g_ctrl = id$(p_ctl);
        if (p_add != undefined && p_add === 1) {
            if (g_ctrl.value) {
                p_value = g_ctrl.value + ',' + p_value;
            }
        }
        if (g_ctrl.tagName === 'INPUT') {
            id$(p_ctl).value = p_value;
        }
        if (g_ctrl.tagName === 'SPAN') {
            id$(p_ctl).innerHTML = p_value;
        }
        if (g_ctrl.tagName === 'SELECT') {
            id$(p_ctl).value = p_value;
        }
    }
}

/**
 *  compute small math in numeric cells
 * @param string value
 * @returns float
 */
function compute_number(value) {
    var retval = 0;

    var exp = new RegExp("^[0-9/*+-.()]+$", "g");
    /*pour éviter un eval() mal intentionné*/
    var res = exp.test(value);
    if (res) {
        /*pour gérer un nombre non valide comme 5..36 ou 5.3.6
         parce qu'il est possible d'entrer plusieurs
         points dans le nombre et eval() lève une exception*/
        try {
            retval = eval(value);
        } catch (e) {
            return parseFloat(value);
        }
        /*pour gérer les divisions par 0*/
        if (retval == Infinity) {
            return 0;
        } else {
            return retval;
        }
    } else {
        return 0;
    }
}

/**
 * format the number change comma to point
 *@param HTML obj
 */
function format_number(obj, p_prec) {
    var precision = 2;
    if (p_prec === undefined) {
        precision = 2;
    } else {
        precision = p_prec;
    }
    var value = obj.value;
    value = value.replace(/ /g, '');
    value = value.replace(/,/g, '.');


    value = compute_number(value);

    value = parseFloat(value);
    if (isNaN(value)) {
        value = 0;
    }
    var arrondi = Math.pow(10, precision);

    value = Math.round(value * arrondi) / arrondi;

    id$(obj).value = value;
}

/**
 * Replace slash , space and minus by dot
 * @param p_object DOM Element date to check
 */
function format_date(p_object) {
    p_object.value = p_object.value.replace(/\//g, '.');
    p_object.value = p_object.value.replace(/-/g, '.');
    p_object.value = p_object.value.replace(/ /g, '.');
    p_object.value = p_object.value.replace(/\.\./g, '.');
    var tmp_value = p_object.value;
    a_split = tmp_value.split('.');
    if (a_split[2] && a_split[2].match(/[0-9]{2}/) && a_split[2].length == 2) {
        a_split[2] = "20" + a_split[2];
        p_object.value = a_split[0] + "." + a_split[1] + "." + a_split[2];
    }
    var nMonth = parseFloat(a_split[1]) - 1;
    var ma_date = new Date(a_split[2], nMonth, a_split[0]);
    if (ma_date.getFullYear() == a_split[2] && ma_date.getMonth() == nMonth && ma_date.getDate() == a_split[0]) {
        return;
    } else {
        new Effect.Highlight(p_object.id, {startcolor: "#ff0000"});
        p_object.value = "";
    }


}

/**
 * check if the object is hidden or show and perform the opposite,
 * show the hidden obj or hide the shown one. With display : flex,
 *@param name of the object
 * @param button id of the button
 * @param rotate : if true with rotate the object of p_button otherwise
 */
function toggleHideShow(p_obj, p_button, rotate) {
    var div_obj = id$(p_obj);
    var stat = div_obj.style.display;

    var str = (id$(p_button)) ? id$(p_button).value : "";

    if (stat === 'none') {
        // specific for the DIV id search_form
        if (div_obj.id == 'search_form') {
            show(p_obj);
        } else {
            id$(p_obj).show()
        }
        str = str.replace(/Afficher/, content[62]);
        id$(p_button).value = str;
    } else {
        // specific for the DIV di search_form
        if (!div_obj.id == 'search_form') {
            hide(p_obj);
        } else {
            id$(p_obj).hide()
        }
        str = str.replace(/Cacher/, content[63]);
        id$(p_button).value = str;
    }
    if (!rotate) return;
    if (stat == "none") {
        id$(p_button).addClassName("icon-up-open-1")
        id$(p_button).removeClassName(" icon-down-open-2")
    } else {
        id$(p_button).removeClassName("icon-up-open-1")
        id$(p_button).addClassName(" icon-down-open-2")

    }

}

/**
 * open popup with the search windows
 *@param p_dossier the dossier where to search
 *@param p_style style of the detail value are E for expert or S for simple
 */
function popup_recherche(p_dossier) {
    var w = window.open("recherche.php?gDossier=" + p_dossier + "&ac=SEARCH", '', 'statusbar=no,scrollbars=yes,toolbar=no');
    w.focus();
}

/**
 * replace the special characters (><'") by their HTML representation
 *@return a string without the offending char.
 */
function unescape_xml(code_html) {
    code_html = code_html.replace(/\&lt;/, '<');
    code_html = code_html.replace(/\&gt;/, '>');
    code_html = code_html.replace(/\&quot;/, '"');
    code_html = code_html.replace(/\&apos;/, "'");
    code_html = code_html.replace(/\&amp;/, '&');
    return code_html;
}

/**
 * Firefox splits the XML into 4K chunk, so to retrieve everything we need
 * to get the different parts thanks textContent
 *@param xmlNode a node (result of var data = =answer.getElementsByTagName('code'))
 *@return all the content of the XML node
 */
function getNodeText(xmlNode) {
    if (!xmlNode)
        return '';
    if (typeof (xmlNode.textContent) != "undefined") {
        return xmlNode.textContent;
    }
    if (xmlNode.firstChild && xmlNode.firstChild.nodeValue)
        return xmlNode.firstChild.nodeValue;
    return "";
}

/**
 * change the periode in the calendar of the dashboard
 *@param object select
 */
function change_month(obj) {
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: {
                gDossier: obj.gDossier,
                op: 'cal',
                "per": obj.value,
                t: obj.type_display,
                notitle: obj.notitle
            },
            onFailure: ajax_misc_failure,
            onSuccess: success_misc
        }
    );

}

/**
 * basic answer to ajax on success, it will fill the DOMID code with
 * the code. In that case, you need to create the object before the Ajax.Request
 *The difference with success box is that
 *@see add_div removeDiv success_box is that the width and height are not changed ajax_misc.php
 *@param code is the ID of the object containing the html (div, button...)
 *@param value is the html code, with it you fill the ctl element
 */

function success_misc(req) {
    try {
        var answer = req.responseXML;
        var html = answer.getElementsByTagName('code');
        if (html.length === 0) {
            var rec = req.responseText;
            alert_box('erreur :' + rec);
        }
        var nodeXml = html[0];
        var code_html = getNodeText(nodeXml);
        code_html = unescape_xml(code_html);
        id$("user_cal").innerHTML = code_html;
    } catch (e) {
        alert_box(e.message);
    }
    try {
        code_html.evalScripts();
    } catch (e) {
        alert_box(content[53] + "\n" + e.message);
    }


}

function loading() {

    var str ='<div style="animation-duration:6s;animation-name:fill_up_loading;animation-iteration-count: infinite;animation-timing-function: linear;align-items: center">';
    str += '<div class="loading_msg"></div>';
    str += '<div class="loading_msg"></div>';
    str += '<div class="loading_msg"></div>';
    str += '<div class="loading_msg"></div>';
    str += '<div class="loading_msg"></div>';
    str +='</div>';

    var str2 = '<div style="animation-duration:6s;animation-name:fill_up_loading;animation-iteration-count: infinite;animation-timing-function: linear;position:relative;top:-50px;animation-delay: 0.7s;">';
    str2 += '<div class="loading_msg"></div>';
    str2 += '<div class="loading_msg"></div>';
    str2 += '<div class="loading_msg"></div>';
    str2 += '<div class="loading_msg"></div>';
    str2 += '<div class="loading_msg"></div>';
    str2 +='</div>';
    return str+str2;
}

function ajax_misc_failure() {
    alert_box(content[53]);
}

/**
 * remove a document_modele
 */
function cat_doc_remove(p_dt_id, p_dossier) {
    var queryString = "gDossier=" + p_dossier + "&op=rem_cat_doc" + "&dt_id=" + p_dt_id;
    var action = new Ajax.Request(
        "ajax_misc.php", {
            method: 'get',
            parameters: queryString,
            onFailure: ajax_misc_failure,
            onSuccess: function (req) {
                try {
                    var answer = req.responseXML;
                    var html = answer.getElementsByTagName('dtid');
                    if (html.length === 0) {
                        var rec = req.responseText;
                        alert_box('erreur <br>' + rec);
                        return;
                    }
                    var nodeXML = html[0];
                    var row_id = getNodeText(nodeXML);
                    if (row_id === 'nok') {
                        var message_node = answer.getElementsByTagName('message');
                        var message_text = getNodeText(message_node[0]);
                        alert_box('erreur <br>' + message_text);
                        return;
                    }
                    id$('row' + row_id).style.textDecoration = "line-through";
                    id$('X' + row_id).style.display = 'none';
                    id$('M' + row_id).style.display = 'none';
                } catch (e) {
                    alert_box(e.message);
                }
            }
        }
    );
}

/**
 * change a document_modele
 */
function cat_doc_change(p_dt_id, p_dossier) {
    var queryString = "gDossier=" + p_dossier + "&op=mod_cat_doc" + "&dt_id=" + p_dt_id;
    var nTop = calcy(posY);
    var nLeft = "200px";
    var str_style = "top:" + nTop + "px;left:" + nLeft + ";width:50em;height:auto";

    removeDiv('change_doc_div');
    waiting_box();
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get', parameters: queryString,
            onFailure: ajax_misc_failure,
            onSuccess: function (req) {
                remove_waiting_box();
                add_div({id: 'change_doc_div', style: str_style, cssclass: 'inner_box', drag: "1"});
                id$('change_doc_div').innerHTML = req.responseText;

            }
        }
    );
}

/**
 * display the popup with vat and explanation
 *@param obj with 4 attributes gdossier, ctl,popup
 *@param p_function_callback callback function to be called after,
 */
function popup_select_tva(obj, p_function_callback) {
    try {
        if (document.getElementById('tva_select')) {
            removeDiv('tva_select');
        }

        var queryString = "gDossier=" + obj.gDossier + "&op=dsp_tva" + "&ctl=" + obj.ctl + '&popup=' + 'tva_select';
        if (obj.jcode)
            queryString += '&code=' + obj.jcode;
        if (obj.compute)
            queryString += '&compute=' + obj.compute;
        if (obj.filter)
            queryString += '&filter=' + obj.filter;

        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get',
                parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req) {
                    try {
                        var answer = req.responseXML;
                        var popup = answer.getElementsByTagName('popup');
                        if (popup.length === 0) {
                            var rec = req.responseText;
                            alert_box('erreur :' + rec);
                        }
                        var html = answer.getElementsByTagName('code');

                        var name_ctl = popup[0].firstChild.nodeValue;
                        var nodeXml = html[0];
                        var code_html = getNodeText(nodeXml);
                        code_html = unescape_xml(code_html);

                        var nTop = posY - 200;
                        var nLeft = "15%";
                        var str_style = "top:" + nTop + "px;left:" + nLeft + ";right:" + nLeft + ";width:55em;height:auto";

                        var popup = {
                            'id': 'tva_select',
                            'cssclass': 'inner_box',
                            'style': str_style,
                            'html': code_html,
                            'drag': false
                        };
                        add_div(popup);
                        id$('lk_tva_select_table').focus();
                        sorttable.makeSortable(id$('tva_select_table'));
                        if (p_function_callback) {
                            p_function_callback.call(null);
                        }
                    } catch (e) {
                        alert_box("success_popup_select_tva " + e.message);
                    }
                }
            }
        );
    } catch (e) {
        alert_box("popup_select_tva " + e.message);
    }
}


/**
 * display the popup with vat and explanation
 *@param obj with 4 attributes gdossier, ctl,popup
 */
function set_tva_label(obj) {
    try {
        var queryString = "gDossier=" + obj.gDossier + "&op=label_tva" + "&id=" + obj.value;
        if (obj.jcode )
            queryString += '&code=' + obj.jcode;
        else if ( obj.getAttribute("jcode") )
            queryString += '&code=' + obj.getAttribute("jcode") ;
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get',
                parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: success_set_tva_label
            }
        );
    } catch (e) {
        alert_box("set_tva_label " + e.message);
    }
}

/**
 * display the popup with vat and explanations
 *@param string req answer from ajax
 */
function success_set_tva_label(req) {
    try {
        var answer = req.responseXML;
        var code = answer.getElementsByTagName('code');
        var value = answer.getElementsByTagName('value');

        if (code.length === 0) {
            var rec = req.responseText;
            alert_box('erreur :' + rec);
        }

        var label_code = code[0].firstChild.nodeValue;
        var label_value = value[0].firstChild.nodeValue;
        set_value(label_code, label_value);
    } catch (e) {
        alert_box("success_set_tva_label " + e.message);
    }

}

/**
 * Create a div without showing it
 * @param {type} obj
 *  the attributes are
 *   - style to add style
 *   - id to add an id
 *   - cssclass to add a class
 *   - html is the content
 *   - drag is the div can be moved
 * @returns html dom element
 * @see add_div
 */
function create_div(obj) {
    try {
        var top = document;
        var elt = null;
        if (!document.getElementById(obj.id)) {
            elt = top.createElement('div');
        } else {
            elt = id$(obj.id);
        }
        if (obj.id) {
            elt.setAttribute('id', obj.id);
        }
        if (obj.style) {
            if (elt.style.setAttribute) { /* IE7 bug */
                elt.style.setAttribute('cssText', obj.style);
            } else { /* good Browser */
                elt.setAttribute('style', obj.style);
            }
        }
        if (obj.cssclass) {
            elt.setAttribute('class', obj.cssclass); /* FF */
            elt.setAttribute('className', obj.cssclass); /* IE */
        }
        if (obj.html) {
            elt.innerHTML = obj.html;
        }

        var bottom_div = document.body;
        elt.hide();
        bottom_div.appendChild(elt);

        /* if ( obj.effect && obj.effect != 'none' ) { Effect.Grow(obj.id,{direction:'top-right',duration:0.1}); }
         else if ( ! obj.effect ){ Effect.Grow(obj.id,{direction:'top-right',duration:0.1}); }*/
        if (obj.drag) {
            aDraggableElement[obj.id] = new Draggable(obj.id, {
                    starteffect: function () {
                        new Effect.Highlight(obj.id, {scroll: window, queue: 'end'});
                    }
                }
            );


        }
        return elt;
    } catch (e) {
        error_message("create_div " + e.message);
    }
}

/**
 * add dynamically a object for AJAX
 *@param obj.
 * the attributes are
 *   - style to add style
 *   - id to add an id
 *   - cssclass to add a class
 *   - html is the content
 *   - drag is the div can be moved
 */
function add_div(obj) {
    try {
        var elt = create_div(obj);
        /* elt.setStyle({visibility:'visible'}); */
        elt.style.visibility = 'visible';
        elt.show();
        return elt;
    } catch (e) {
        alert_box("add_div " + e.message);
    }
}

/**
 * remove a object created with add_div
 * @param str_elt string id of the elt
 */
function removeDiv(str_elt) {
    if (document.getElementById(str_elt)) {
        document.body.removeChild(id$(str_elt));
    }
    // if reloaded if asked the window will be reloaded when
    // the box is closed
    if (ask_reload === 1) {
        // avoid POST window.location = window.location.href;
        window.location.reload();
    }
}

function waiting_node() {
    id$('info_div').innerHTML = 'Un instant';
    id$('info_div').style.display = "block";
}

/**
 *show a box while loading
 *must be remove when ajax is successfull
 * the id is wait_box
 */
function waiting_box() {
    var obj = {
        id: 'wait_box', html: loading() + '<p>' + content[65] + '</p>'
    };
    var y = fixed_position(10, 250)
    obj.style = y + ";width:281px;margin-left:40%;";
    if (document.getElementById('wait_box')) {
        removeDiv('wait_box');
    }
    waiting_node();
    add_div(obj);


}

/**
 * call add_div to add a DIV and after call the ajax
 * the queryString, the callback for function for success and error management
 * the method is always GET
 *@param obj, the mandatory attributes are
 *  - obj.qs querystring
 *  - obj.js_success callback function in javascript for handling the xml answer
 *  - obj.js_error callback function for error
 *  - obj.callback the php file to call
 *  - obj.fixed optional let you determine the position, otherwise works like IPopup
 *@see add_div IBox
 */
function show_box(obj) {
    add_div(obj);
    if (!obj.fixed) {
        id$(obj.id).style.top = calcy(40) + "px";
        show(obj.id);
    } else {
        show(obj.id);
    }

    var action = new Ajax.Request(
        obj.callback,
        {
            method: 'GET',
            parameters: obj.qs,
            onFailure: eval(obj.js_error),
            onSuccess: eval(obj.js_success)
        });
}

/**
 * receive answer from ajax and just display it into the IBox
 * XML must contains at least 2 fields : ctl is the ID of the IBOX and
 * code is the HTML to put in it
 *@see fill_box
 */
function success_box(req, json) {
    try {
        var answer = req.responseXML;
        var a = answer.getElementsByTagName('ctl');
        var html = answer.getElementsByTagName('code');
        if (a.length === 0) {
            var rec = req.responseText;
            alert_box(content[48] + rec);
        }
        var name_ctl = a[0].firstChild.nodeValue;
        var code_html = getNodeText(html[0]);

        code_html = unescape_xml(code_html);
        id$(name_ctl).innerHTML = code_html;
        id$(name_ctl).style.height = 'auto';

        if (name_ctl == 'popup')
            id$(name_ctl).style.width = 'auto';
    } catch (e) {
        alert_box("success_box" + e.message);
    }
    try {
        code_html.evalScripts();
    } catch (e) {
        alert_box(content[53] + "\n" + e.message);
    }
}

function error_box() {
    alert_box(content[53]);
}

/**
 * show the ledger choice
 */
function show_ledger_choice(json_obj) {
    try {
        waiting_box();
        var i = 0;
        var query = "gDossier=" + json_obj.dossier + '&type=' + json_obj.type + '&div=' + json_obj.div + '&op=ledger_show';
        query = query + '&nbjrn=' + id$(json_obj.div + 'nb_jrn').value;
        query = query + '&all_type=' + json_obj.all_type;
        for (i = 0; i < id$(json_obj.div + 'nb_jrn').value; i++) {
            query = query + "&r_jrn[]=" + id$(json_obj.div + 'r_jrn[' + i + ']').value;
        }
        query = encodeURI(query);
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get',
                parameters: query,
                onFailure: ajax_misc_failure,
                onSuccess: function (req, json) {
                    try {
                        if (req.responseText === 'NOCONX') {
                            reconnect();
                            return;
                        }
                        var obj = {
                            id: json_obj.div + 'jrn_search',
                            cssclass: 'inner_box',
                            style: ';position:absolute;width:auto;z-index:20;margin-left:20%',
                            drag: 1
                        };
                        //var y=calcy(posY);
                        var y = posY;

                        obj.style = "top:" + y + 'px;' + obj.style;
                        /* if ( json_obj.class )
                         {
                         obj.cssclass=json_obj.class;
                         }*/
                        add_div(obj);


                        var answer = req.responseXML;
                        var a = answer.getElementsByTagName('ctl');
                        var html = answer.getElementsByTagName('code');
                        if (a.length === 0) {
                            var rec = req.responseText;
                            alert_box('erreur :' + rec);
                        }
                        var name_ctl = a[0].firstChild.nodeValue;
                        var code_html = getNodeText(html[0]);

                        code_html = unescape_xml(code_html);
                        remove_waiting_box();
                        id$(obj.id).innerHTML = code_html;

                    } catch (e) {
                        alert_box("show_ledger_callback" + e.message);
                    }
                    try {
                        code_html.evalScripts();
                    } catch (e) {
                        alert_box(content[53] + "\n" + e.message);
                    }

                }

            }
        );
    } catch (e) {
        alert_box('show_ledger_choice' + e.message);
    }
}

/**
 * hide the ledger choice
 */
function hide_ledger_choice(p_frm_search) {
    try {
        var nb = id$(p_frm_search).nb_jrn.value;
        var div = "";
        if (document.getElementById(p_frm_search).div) {
            div = id$(p_frm_search).div.value;
        }
        var i = 0;
        var str = "";
        var name = "";
        var n_name = "";
        var sel = 0;
        for (i = 0; i < nb; i++) {
            n_name = div + "r_jrn[" + sel + "]";
            name = div + "r_jrn" + i;
            if (document.getElementById(name).checked) {
                str += '<input type="hidden" id="' + n_name + '" name="' + n_name + '" value="' + id$(name).value + '">';
                sel++;
            }
        }
        str += '<input type="hidden" name="' + div + 'nb_jrn" id="' + div + 'nb_jrn" value="' + sel + '">';
        id$('ledger_id' + div).innerHTML = str;
        removeDiv(div + 'jrn_search');
        return false;
    } catch (e) {
        alert_box('hide_ledger_choice' + e.message);
        return false;
    }

}

/**
 * show the cat of ledger choice
 */
function show_cat_choice() {
    id$('div_cat').style.visibility = 'visible';
}

/**
 * hide the cat of ledger choice
 */
function hide_cat_choice() {
    id$('div_cat').style.visibility = 'hidden';
}

/**
 * add a row for the forecast item
 */
function for_add_row(tableid) {
    style = 'class="input_text"';
    var mytable = id$(tableid).tBodies[0];
    var nNumberRow = mytable.rows.length;
    var oRow = mytable.insertRow(nNumberRow);
    var rowToCopy = mytable.rows[1];
    var nNumberCell = rowToCopy.cells.length;
    var nb = id$("nbrow");
    var oNewRow = mytable.insertRow(nNumberRow);
    for (var e = 0; e < nNumberCell; e++) {
        var newCell = oRow.insertCell(e);
        var tt = rowToCopy.cells[e].innerHTML;
        new_tt = tt.replace(/an_cat0/g, "an_cat" + nb.value);
        new_tt = new_tt.replace(/an_cat_acc0/g, "an_cat_acc" + nb.value);
        new_tt = new_tt.replace(/an_qc0/g, "an_qc" + nb.value);
        new_tt = new_tt.replace(/an_label0/g, "an_label" + nb.value);
        new_tt = new_tt.replace(/month0/g, "month" + nb.value);
        new_tt = new_tt.replace(/an_cat_amount0/g, "an_cat_amount" + nb.value);
        new_tt = new_tt.replace(/an_deb0/g, "an_deb" + nb.value);
        newCell.innerHTML = new_tt;
        new_tt.evalScripts();
    }
    id$("an_cat_acc" + nb.value).value = "";
    id$("an_qc" + nb.value).value = "";
    id$("an_label" + nb.value).value = "";
    id$("an_cat_amount" + nb.value).value = "0";
    nb.value++;
}

/**
 * toggle all the checkbox in a given form
 * @param form_id id of the form
 */
function toggle_checkbox(form_id) {
    var form = id$(form_id);
    for (var i = 0; i < form.length; i++) {
        var e = form.elements[i];
        if (e.type === 'checkbox') {
            if (e.checked === true) {
                e.checked = false;
            } else {
                e.checked = true;
            }
        }
    }
}

/**
 * select all the checkbox in a given form
 * @param form_id id of the form
 */
function select_checkbox(form_id) {
    var form = id$(form_id);
    for (var i = 0; i < form.length; i++) {
        var e = form.elements[i];
        if (e.type === 'checkbox') {
            e.checked = true;
        }
    }
}

/**
 * select all the checkbox in a given form if the specific attribute
 * has the given value
 * @param form_id id of the form
 * @param attribute name
 * @param attribute value
 */
function select_checkbox_attribute(form_id, p_attribute_name, p_attribute_value) {
    var form = id$(form_id);
    for (var i = 0; i < form.length; i++) {
        var e = form.elements[i];
        if (e.type === 'checkbox' && e.getAttribute(p_attribute_name) == p_attribute_value) {
            e.checked = true;
        }
    }
}

/**
 * unselect all the checkbox in a given form
 * @param form_id id of the form
 */
function unselect_checkbox(form_id) {
    var form = id$(form_id);
    for (var i = 0; i < form.length; i++) {
        var e = form.elements[i];
        if (e.type === 'checkbox') {
            e.checked = false;
        }
    }
}

/**
 * show the calculator
 */
function show_calc() {
    if (document.getElementById('calc1')) {
        this.document.getElementById('inp').value = "";
        this.document.getElementById('inp').focus();
        return;
    }
    var sid = 'calc1';
    var shtml = '';
    shtml += "<div class=\"bxbutton\">";
    shtml += '<a class="icon" onclick="pin(\'calc1\')" id="pin_calc1">&#xf047;</a>	<a onclick="removeDiv(\'calc1\');" href="javascript:void(0)" title="" class="icon">&#10761;</a>';
    shtml += "</div>";
    shtml += '   <h2 class="title">' + content[66] + '</h2>';
    shtml += '<form name="calc_line"  method="GET" onSubmit="cal();return false;" >' + content[68] + '<input class="input_text" type="text" id="inp" name="calculator"> <input type="button" value="Efface" class="button" onClick="Clean();return false;" > <input type="button" value="Efface historique" class="button" onClick="CleanHistory();return false;" > <input type="button" class="button" value="Fermer" onClick="removeDiv(\'calc1\')" >';
    shtml += '</form><span class="highligth" style="display:block" id="sub_total">  ' + content[67] + '  </span><span style="display:block"  id="listing"> </span>';

    var obj = {
        id: sid, html: shtml,
        drag: false, style: 'z-index:98'
    };
    add_div(obj);
    this.document.getElementById('inp').focus();
}

function display_periode(p_dossier, p_id) {

    try {
        var queryString = "gDossier=" + p_dossier + "&op=input_per" + "&p_id=" + p_id;
        var popup = {
            'id': 'mod_periode',
            'cssclass': 'inner_box',
            'html': loading(),
            'style': 'width:30em',
            'drag': true
        };
        if (!document.getElementById('mod_periode')) {
            add_div(popup);
        }
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get',
                parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: success_display_periode
            }
        );
        id$('mod_periode').style.top = (posY - 70) + "px";
        id$('mod_periode').style.left = (posX - 70) + "px";
    } catch (e) {
        alert_box("display_periode " + e.message);
    }

}

function success_display_periode(req) {
    try {

        var answer = req.responseXML;
        var html = answer.getElementsByTagName('data');

        if (html.length === 0) {
            var rec = req.responseText;
            alert_box('erreur :' + rec);
        }

        var code_html = getNodeText(html[0]);
        code_html = unescape_xml(code_html);

        id$('mod_periode').innerHTML = code_html;
    } catch (e) {
        alert_box("success_display_periode".e.message);
    }
    try {
        code_html.evalScripts();
    } catch (e) {
        alert_box(content[53] + "\n" + e.message);
    }

}

function save_periode(obj) {
    try {
        var queryString = id$(obj).serialize() + "&op=save_per";

        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'post',
                parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: success_display_periode
            }
        );

    } catch (e) {
        alert_box("display_periode " + e.message);
    }

    return false;
}

/**
 * basic answer to ajax on success, it will fill the ctl with
 * the code. In that case, you need to create the object before the Ajax.Request
 *The difference with success box is that
 *@see add_div removeDiv success_box is that the width and height are not changed
 *@param ctl is the ID of the object containing the html (div, button...)
 *@param code is the html code, with it you fill the ctl element
 */
function fill_box(req) {
    try {
        if (req.responseText == 'NOCONX') {
            reconnect();
            return;
        }
        remove_waiting_box();

        var answer = req.responseXML;
        var a = answer.getElementsByTagName('ctl');
        var html = answer.getElementsByTagName('code');
        if (a.length === 0) {
            var rec = req.responseText;
            alert_box('erreur :' + rec);
        }
        var name_ctl = a[0].firstChild.nodeValue;
        var code_html = getNodeText(html[0]); // Firefox ne prend que les 4096 car.
        code_html = unescape_xml(code_html);
        id$(name_ctl).innerHTML = code_html;
    } catch (e) {
        alert_box(e.message);
        if (console) {
            console.error(e);
            console.error("log answer = " + req.responseText);
        }
    }
    try {
        code_html.evalScripts();
    } catch (e) {
        if (console) {
            console.error(e);
            console.error("log answer = " + req.responseText);
        }
        alert_box(content[53] + "\n" + e.message);
    }


}

/**
 *display a popin to  let you modified a predefined operation
 *@param dossier_id
 *@param od_id from table op_predef
 */
function mod_predf_op(dossier_id, od_id, p_ledger) {
    var target = "mod_predf_op";
    removeDiv(target);
    var str_style = "top:10%;left:2%;width:96%";

    var div = {id: target, cssclass: 'inner_box', style: str_style, html: loading(), drag: 1};

    add_div(div);

    var qs = "gDossier=" + dossier_id + '&op=mod_predf&id=' + od_id + '&ledger_id=' + p_ledger;

    var action = new Ajax.Request('ajax_misc.php',
        {
            method: 'get',
            parameters: qs,
            onFailure: null,
            onSuccess: fill_box
        }
    );

}

function save_predf_op(obj) {
    waiting_box();
    var querystring = id$(obj).serialize() + '&op=save_predf';
    // Create a ajax request to get all the person
    var action = new Ajax.Request('ajax_misc.php',
        {
            method: 'post',
            parameters: querystring,
            onFailure: null,
            onSuccess: refresh_window
        }
    );

    return false;
}

/**
 *ctl_concern is the widget to update
 *amount_id is either a html obj. or an amount and the field tiers if given
 * @param {int} dossier
 * @param {string} ctl_concern DOM id that receive the number
 * @param {float or string} amount_id Amount or DOM Id of the element containing the amount
 * @param {float} ledger
 * @param {type} p_id_targetDom Element (div) where to display the search result
 * @param p_tiers id of the Tiers
 * @returns {undefined}
 */
function search_reconcile(dossier, ctl_concern, amount_id, ledger, p_id_target, p_tiers) {
    if (amount_id === undefined) {
        amount_id = 0;
    } else if (document.getElementById(amount_id)) {
        if (id$(amount_id).value) {
            amount_id = id$(amount_id).value;
        } else if
        (id$(amount_id).innerHTML) {
            amount_id = id$(amount_id).innerHTML;
        }
    }
    var tiers = "";
    if (p_tiers)
        tiers = p_tiers;
    var target = "";
    if (p_id_target != "") {
        target = p_id_target;
    } else {
        target = "search" + layer;
        removeDiv(target);
    }
    var str_style = fixed_position(77, 99);
    str_style += ";width:92%;overflow:auto;";
    waiting_box();
    var hide_operation = id$(ctl_concern).getAttribute("hide_operation");
    var single_operation = id$(ctl_concern).getAttribute("single_operation");

    var param_send = {
        gDossier: dossier,
        ctlc: ctl_concern,
        op: 'search_op',
        ac: 'JSSEARCH',
        amount_id: amount_id,
        ledger: ledger,
        target: target,
        tiers: tiers,
        hide_operation: hide_operation,
        single_operation: single_operation
    };

    var qs = encodeJSON(param_send);

    var action = new Ajax.Request('ajax_misc.php',
        {
            method: 'get',
            parameters: qs,
            onFailure: null,
            onSuccess: function (req) {
                remove_waiting_box();
                var div = {id: target, cssclass: 'inner_box', style: str_style, drag: 0};
                add_div(div);
                id$(target).innerHTML = req.responseText;
                req.responseText.evalScripts();
            }
        }
    );
}

/**
 * search in a popin obj if the object form,
 * @param obj DOM of the FORM
 */
function search_operation(obj) {
    try {
        var dossier = id$('gDossier').value;
        waiting_box();
        var target = "search" + layer;
        if (obj["target"]) {
            target = obj["target"].value;
        }
        var qs = Form.serialize('search_form_ajx') + "&op=search_op";
        var action = new Ajax.Request('ajax_misc.php',
            {
                method: 'get',
                parameters: qs,
                onFailure: null,
                onSuccess: function (req) {
                    remove_waiting_box();
                    id$(target).innerHTML = req.responseText;
                    req.responseText.evalScripts();
                }
            }
        );
    } catch (e) {
        remove_waiting_box();
        alert_box(e.message);
    }
}

/**
 * Update the field e_concerned, from class_iconcerned
 * Value is the field where to put the quick-code but only if one checkbox has been
 * selected
 * @param {DOM Element} obj : DOM FORM ,
 *      - element : ctlc : will contain the JRN.JR_ID ,
 *      - tiers : the name of the counterparty
 *      - target : DGBOX displaying the search result
 *
 * @returns {undefined}
 */
function set_reconcile(obj) {

    try {
        var ctlc = obj.elements['ctlc'];
        var tiers = obj.elements['tiers'];
        if (!obj.elements['target'])
            return;
        var target = obj.elements['target'].value;
        var single_operation = obj.elements['single_operation'].value;
        for (var e = 0; e < obj.elements.length; e++) {

            var elmt = obj.elements[e];
            if (elmt.type === "checkbox") {
                if (elmt.checked === true) {
                    var str_name = elmt.name;
                    var nValue = str_name.replace("jr_concerned", "");
                    if (id$(ctlc.value).value != '') {
                        id$(ctlc.value).value += ',';

                    } else {

                        if (tiers && tiers.value != "") {
                            id$(tiers.value).value = elmt.value;
                            /* set the name */
                            new Ajax.Request("fid.php", {
                                method: "get",
                                parameters: {gDossier: obj.elements['gDossier'].value, "FID": elmt.value},
                                onSuccess: function (req) {
                                    // find the row number
                                    //tiers.value = e_othern
                                    var tiers_card = new String(tiers.value);
                                    var num = tiers_card.replace("e_other", "");
                                    var tiers_name_id = "e_other" + "_name" + num;
                                    var answer = req.responseText.evalJSON();
                                    id$(tiers_name_id).value = answer["name"];
                                }
                            });
                        }
                    }
                    if (single_operation == 0) {
                        id$(ctlc.value).value += nValue;
                    } else {
                        id$(ctlc.value).value = nValue;

                    }
                }
            }
        }
        removeDiv(obj.elements['target'].value);
    } catch (e) {
        alert_box(e.message)
    }
}

function remove_waiting_node() {
    id$('info_div').innerHTML = "";
    id$('info_div').style.display = "none";

}

function remove_waiting_box() {
    if (document.getElementById('wait_box')) {
        Effect.Fade('wait_box', {duration: 0.6});
    }

    remove_waiting_node();
}

/**
 * Show all the detail of a profile : Menu, Management, Repository and
 * let the user to modify it
 * @param {type} gDossier
 * @param {type} profile_id
 * @returns {undefined}
 */
function get_profile_detail(gDossier, profile_id) {
    waiting_box();
    var qs = "op=display_profile&gDossier=" + gDossier + "&p_id=" + profile_id + "&ctl=detail_profile";
    var action = new Ajax.Request('ajax_misc.php',
        {
            method: 'get',
            parameters: qs,
            onFailure: null,
            onSuccess: function (req) {
                remove_waiting_box();
                id$('list_profile').hide();
                id$('detail_profile').innerHTML = req.responseText;
                req.responseText.evalScripts();
                id$('detail_profile').show();
                if (profile_id != "-1")
                    profile_show('profile_gen_div');
            }
        }
    );
}

function get_profile_detail_success_obsolete(xml) {
    remove_waiting_box();

}

/**
 *  compute the string to position a div in a fixed way
 * @return string
 */
function fixed_position(p_sx, p_sy) {
    var sx = p_sx;
    var sy = calcy(p_sy);

    var str_style = "top:" + sy + "px;left:" + sx + "px;position:absolute";
    return str_style;

}

/**
 * compute Y even if the windows has scrolled down or up
 *@return the correct Y position
 */
function calcy(p_sy) {
    var sy = p_sy;
    if (window.pageYOffset) {
        sy = window.pageYOffset + p_sy;
    } else {
        sy = document.documentElement.scrollTop + p_sy;
    }

    return sy;

}

/**
 *  display a box with the menu option
 * @param {type} gdossier
 * @param {type} pm_id
 * @returns {undefined}
 */
function mod_menu(gdossier, pm_id) {
    waiting_box();
    removeDiv('divdm' + pm_id);
    var qs = "op=det_menu&gDossier=" + gdossier + "&pm_id=" + pm_id + "&ctl=divdm" + pm_id;
    var pos = fixed_position(50, 250);
    var action = new Ajax.Request('ajax_misc.php',
        {
            method: 'get',
            parameters: qs,
            onFailure: null,
            onSuccess: function (req) {
                try {
                    remove_waiting_box();
                    add_div({id: "divdm" + pm_id, drag: 1, cssclass: "inner_box", style: pos});
                    id$('divdm' + pm_id).innerHTML = req.responseText;
                } catch (e) {
                    alert_box(e.message);
                }
            }
        }
    );
}

/**
 * Display the submenu of a menu or a module, used in setting the menu
 *
 * @param {type} p_dossier
 * @param {type} p_profile
 * @param {type} p_dep
 * @returns {undefined}
 */
function display_sub_menu(p_dossier, p_profile, p_dep, p_level) {
    waiting_box();
    new Ajax.Request('ajax_misc.php',
        {
            method: 'get',
            parameters: {
                op: 'display_submenu',
                gDossier: p_dossier,
                dep: p_dep,
                p_profile: p_profile,
                p_level: p_level
            },
            onSuccess: function (req) {
                try {
                    remove_waiting_box();
                    if (id$('menu_table').rows.length > p_level) {
                        id$('menu_table').rows[1].remove();
                    }
                    id$('sub' + p_dep).addClassName("selectedmenu");
                    var new_row = document.createElement('TR');
                    new_row.innerHTML = req.responseText;
                    id$('menu_table').appendChild(new_row);
                } catch (e) {
                    alert_box(e.message);
                }
            }
        })
}

/**
 * in C0PROFL, ask to confirm before removing a submenu and its children
 * @param {type} p_dossier
 * @param {type} profile_menu_id
 * @returns {undefined}
 */
function remove_sub_menu(p_dossier, profile_menu_id) {
    confirm_box(null, content[47],
        function () {
            waiting_box();
            new Ajax.Request('ajax_misc.php',
                {
                    method: 'get',
                    parameters: {
                        op: 'remove_submenu', gDossier: p_dossier,
                        p_profile_menu_id: profile_menu_id
                    },
                    onSuccess: function (req) {
                        try {
                            remove_waiting_box();
                            id$('sub' + profile_menu_id).remove();
                            if (id$('menu_table').rows.length > 1) {
                                id$('menu_table').rows[1].remove();
                            }

                        } catch (e) {
                            alert_box(e.message);
                        }
                    }
                }
            )
        });

}

/**
 *  add a menu to a profile, propose only the available menu
 * @param obj json object
 *   - dossier  : ,
 *   - p_id : profile id ,
 *   - type : Type of menu are "pr" for Printing "me" for plain menu
 *   - p_level : level of menu (0 -> module,1-> top menu, 2->submenu)
 *   - dep : the parent menu id  (pm_id)
 *
 */
function add_menu(obj) {
    var pdossier = obj.dossier;
    var p_id = obj.p_id;
    var p_type = obj.type;

    waiting_box();
    removeDiv('divdm' + p_id);
    var pos = fixed_position(250, 150) + ";width:50%;";
    var action = new Ajax.Request('ajax_misc.php',
        {
            method: 'get',
            parameters: {
                op: 'add_menu',
                'gDossier': pdossier,
                'p_id': p_id,
                'ctl': 'divdm' + p_id,
                'type': p_type,
                'dep': obj.dep,
                'p_level': obj.p_level
            },
            onFailure: null,
            onSuccess: function (req) {
                try {
                    remove_waiting_box();
                    add_div({id: "divdm" + p_id, drag: 1, "cssclass": "inner_box", "style": pos});
                    id$('divdm' + p_id).innerHTML = req.responseText;
                } catch (e) {
                    alert_box(e.message);
                }
            }
        }
    );
}

/**
 *  Display a box to enter data for adding a new plugin from
 * the CFGMENU
 * @param {type} p_dossier
 * @returns {undefined}
 */
function add_plugin(p_dossier) {
    waiting_box();
    removeDiv('divplugin');
    var qs = "op=add_plugin&gDossier=" + p_dossier + "&ctl=divplugin";

    var action = new Ajax.Request('ajax_misc.php',
        {
            method: 'get',
            parameters: qs,
            onFailure: null,
            onSuccess: function (req) {
                try {
                    remove_waiting_box();
                    var pos = fixed_position(250, 150) + ";width:30%";
                    add_div({id: "divplugin", drag: 1, cssclass: "inner_box", style: pos});
                    id$('divplugin').innerHTML = req.responseText;
                } catch (e) {
                    alert_box(e.message);
                }
            }
        }
    );
}

/**
 * Modify a menu
 * @param {type} p_dossier
 * @param {type} me_code
 * @returns {undefined}
 */
function mod_plugin(p_dossier, me_code) {
    waiting_box();
    removeDiv('divplugin');
    var qs = "op=mod_plugin&gDossier=" + p_dossier + "&ctl=divplugin&me_code=" + me_code;

    var action = new Ajax.Request('ajax_misc.php',
        {
            method: 'get',
            parameters: qs,
            onFailure: null,
            onSuccess: function (req) {
                try {
                    remove_waiting_box();
                    var pos = fixed_position(250, 150) + ";width:30%";
                    add_div({id: "divplugin", drag: 1, cssclass: "inner_box", style: pos});
                    id$('divplugin').innerHTML = req.responseText;

                } catch (e) {
                    alert_box(e.message);
                }
            }
        }
    );
}

function create_menu(p_dossier) {
    waiting_box();
    removeDiv('divmenu');
    var qs = "op=create_menu&gDossier=" + p_dossier + "&ctl=divmenu";

    var action = new Ajax.Request('ajax_misc.php',
        {
            method: 'get',
            parameters: qs,
            onFailure: null,
            onSuccess: function (req) {
                try {
                    remove_waiting_box();
                    var pos = fixed_position(250, 150) + ";width:30%";
                    add_div({
                        id: "divmenu",
                        drag: 1,
                        cssclass: "inner_box",
                        style: pos
                    });
                    id$('divmenu').innerHTML = req.responseText;
                } catch (e) {
                    alert_box(e.message);
                }
            }
        }
    );
}

function modify_menu(p_dossier, me_code) {
    waiting_box();
    removeDiv('divmenu');
    var qs = "op=modify_menu&gDossier=" + p_dossier + "&ctl=divmenu&me_code=" + me_code;

    var action = new Ajax.Request('ajax_misc.php',
        {
            method: 'get',
            parameters: qs,
            onFailure: null,
            onSuccess: function (req) {
                try {
                    remove_waiting_box();
                    var pos = fixed_position(250, 150) + ";width:30%";
                    add_div({
                        id: "divmenu",
                        drag: 1,
                        cssclass: "inner_box",
                        style: pos
                    });
                    id$('divmenu').innerHTML = req.responseText;

                } catch (e) {
                    alert_box(e.message);
                }
            }
        }
    );
}

function get_properties(obj) {
    var a_array = [];
    var s_type = "[" + typeof obj + "]";
    for (var m in obj) {
        a_array.push(m);
    }
    alert_box(s_type + a_array.join(","));
}

/**
 *  add a line in the form for the report
 * @param p_dossier dossier id to connect
 */
function rapport_add_row(p_dossier) {
    style = 'style="border: 1px solid blue;"';
    var table = id$("rap1");
    var line = table.rows.length;

    var row = table.insertRow(line);
    // left cell
    var cellPos = row.insertCell(0);
    cellPos.innerHTML = '<input type="text" ' + style + ' size="3" id="pos' + line + '" name="pos' + line + '" value="' + line + '">';

    // right cell
    var cellName = row.insertCell(1);
    cellName.innerHTML = '<input type="text" ' + style + ' size="40" id="text' + line + '" name="text' + line + '">';

    // button + formula
    var cellbutton = row.insertCell(2);
    var but_html = table.rows[1].cells[2].innerHTML;
    but_html = but_html.replace(/form0/g, "form" + line);
    cellbutton.innerHTML = but_html;
    but_html.evalScripts();

    id$('form' + line).value = '';
}

/**
 * Search an action in an inner box
 */
function search_action(dossier, ctl_concern) {
    try {
        waiting_box();
        var dossier = id$('gDossier').value;

        var target = "search_action_div";
        removeDiv(target);
        var str_style = fixed_position(77, 99);

        var div = {id: target, cssclass: 'inner_box', style: str_style, html: loading(), drag: 1};


        var target = {
            gDossier: dossier,
            ctlc: ctl_concern,
            op: 'search_action',
            ctl: target
        };

        var qs = encodeJSON(target);

        var action = new Ajax.Request('ajax_misc.php',
            {
                method: 'get',
                parameters: qs,
                onFailure: null,
                onSuccess: function (req) {
                    try {
                        remove_waiting_box();
                        add_div(div);
                        id$('search_action_div').innerHTML = req.responseText;
                        req.responseText.evalScripts();
                    } catch (e) {
                        alert_box(e.message);
                    }
                }
            }
        );
    } catch (e) {
        alert_box(e.message);
    }
}

function result_search_action(obj) {
    try {
        var queryString = id$(obj).serialize() + "&op=search_action";
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get',
                parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req) {
                    try {
                        remove_waiting_box();
                        id$('search_action_div').innerHTML = req.responseText;
                        req.responseText.evalScripts();
                    } catch (e) {
                        alert_box(e.message);
                    }
                }
            }
        )

    } catch (e) {
        alert_box("display_periode " + e.message);
    }

    return false;
}

function set_action_related(p_obj) {

    try {
        var obj = id$(p_obj);
        var ctlc = obj.elements['ctlc'];

        for (var e = 0; e < obj.elements.length; e++) {

            var elmt = obj.elements[e];
            if (elmt.type === "checkbox") {
                if (elmt.checked === true) {
                    var str_name = elmt.name;
                    var nValue = elmt.value;
                    if (id$(ctlc.value).value != '') {
                        id$(ctlc.value).value += ',';
                    }
                    id$(ctlc.value).value += nValue;
                }
            }
        }
        removeDiv('search_action_div');
        return false;
    } catch (e) {
        alert_box(e.message);
        return false;
    }
}

/**
 * Show a form to modify or add a new repository
 *@param p_dossier
 *@param r_id : repository id
 */
function stock_repo_change(p_dossier, r_id) {
    var queryString = "gDossier=" + p_dossier + "&op=mod_stock_repo" + "&r_id=" + r_id;
    var nTop = calcy(posY);
    var nLeft = "10.1562%";
    var str_style = "top:" + nTop + "px;left:" + nLeft + ";height:auto;width:auto";

    removeDiv('change_stock_repo_div');
    waiting_box();
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get', parameters: queryString,
            onFailure: ajax_misc_failure,
            onSuccess: function (req) {
                remove_waiting_box();
                add_div({id: 'change_stock_repo_div', style: str_style, cssclass: 'inner_box', drag: "1"});
                id$('change_stock_repo_div').innerHTML = req.responseText;

            }
        }
    );
}

function stock_inv_detail(p_dossier, p_id) {
    var queryString = "gDossier=" + p_dossier + "&op=view_mod_stock" + "&c_id=" + p_id + "&ctl=view_mod_stock_div";
    var nTop = calcy(posY);
    var nLeft = "10%";
    var str_style = "top:" + nTop + "px;left:" + nLeft + ";width:80%;";

    removeDiv('view_mod_stock_div');
    waiting_box();
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get', parameters: queryString,
            onFailure: ajax_misc_failure,
            onSuccess: function (req) {
                remove_waiting_box();
                add_div({id: 'view_mod_stock_div', style: str_style, cssclass: 'inner_box', drag: "1"});
                id$('view_mod_stock_div').innerHTML = req.responseText;
                req.responseText.evalScripts();
            }
        }
    );
}

function show_fin_chdate(obj_id) {
    try {
        var ch = id$(obj_id).options[id$(obj_id).selectedIndex].value;
        if (ch == 2) {
            id$('chdate_ext').hide();
            id$('thdate').show();
        }
        if (ch == 1) {
            id$('chdate_ext').show();
            id$('thdate').hide();
        }
        var nb = id$('nb_item').value;
        for (i = 0; i < nb; i++) {
            if (document.getElementById('tdchdate' + i)) {
                if (ch == 2) {
                    id$('tdchdate' + i).show();
                }
                if (ch == 1) {
                    id$('tdchdate' + i).hide();

                }
            }
        }
    } catch (e) {
        alert_box(e.message);
    }
}

/**
 * tab menu for the profile parameter
 */
function profile_show(p_div) {
    try {
        var div = ['profile_gen_div', 'profile_menu_div', 'profile_print_div', 'profile_gestion_div', 'profile_repo_div', 'profile_menu_mobile_div'];
        for (var r = 0; r < div.length; r++) {
            id$(div[r]).hide();
        }
        id$(p_div).show();
    } catch (e) {
        alert_box(e.message);
    }
}

function detail_category_show(p_div, p_dossier, p_id) {
    id$(p_div).show();
    waiting_box();
    id$('detail_category_div').innerHTML = "";
    var queryString = "gDossier=" + p_dossier + "&id=" + p_id + "&op=fddetail";
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get', parameters: queryString,
            onFailure: ajax_misc_failure,
            onSuccess: function (req) {
                remove_waiting_box();
                id$('list_cat_div').hide();
                id$('detail_category_div').innerHTML = req.responseText;
                id$('detail_category_div').show();
                req.responseText.evalScripts();
            }
        }
    );
}

/**
 * check that the form is correct for a new category of card
 */
function check_new_category()
{
    if ( id$('nom_mod_id').value.trim()=="") {
        new Effect.Highlight('nom_mod_id',{startcolor:"#ff0000"});
        smoke.alert('Nom catégorie obligatoire');
        return false;
    }
    var TemplateCard= document.getElementsByName('FICHE_REF');
    for (i = 0;i< TemplateCard.length;i++) {
        if (TemplateCard[i].checked) return true;
    }
    new Effect.Highlight('template_category_ck',{startcolor:"#ff0000"});
    smoke.alert('Choisissez une catégorie');
    return false;
}
/**
 *  check if the parameter is a valid a valid date or not, returns true if it is valid otherwise
 * false
 * @param p_str_date the string of the date (format DD.MM.YYYY)
 */
function check_date(p_str_date) {
    var format = /^\d{2}\.\d{2}\.\d{4}$/;
    if (!format.test(p_str_date)) {
        return false;
    } else {
        var date_temp = p_str_date.split('.');
        var nMonth = parseFloat(date_temp[1]) - 1;
        var ma_date = new Date(date_temp[2], nMonth, date_temp[0]);
        if (ma_date.getFullYear() == date_temp[2] && ma_date.getMonth() == nMonth && ma_date.getDate() == date_temp[0]) {
            return true;
        } else {
            return false;
        }
    }

}

/**
 *  get the string in the id and check if the date is valid
 * @param p_id_date is the id of the element to check
 * @return true if the date is valid
 * @see check_date
 */
function check_date_id(p_id_date) {
    var str_date = id$(p_id_date).value;
    return check_date(str_date);
}

/**
 *
 * @param ag_id to view
 * @param dossier is the folder
 * @param modify : show the modify button values : 0 for no 1 for yes
 */
function view_action(ag_id, dossier, modify) {
    waiting_box();
    layer++;
    id = 'action' + layer;

    querystring = 'gDossier=' + dossier + '&op=vw_action&ag_id=' + ag_id + '&div=' + id + '&mod=' + modify;
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: querystring,
            onFailure: error_box,
            onSuccess: function (req) {
                try {
                    if (req.responseText === 'NOCONX') {
                        reconnect();
                        return;
                    }
                    remove_waiting_box();
                    var answer = req.responseXML;
                    var ctl = answer.getElementsByTagName('ctl');
                    if (ctl.length == 0) {
                        throw 'ajax failed ctl view_action';
                    }
                    var ctl_txt = getNodeText(ctl[0]);
                    var html = answer.getElementsByTagName('code');
                    if (html.length === 0) {
                        var rec = req.responseText;
                        throw 'ajax failed  html view_action';
                    }
                    var code_html = getNodeText(html[0]);
                    code_html = unescape_xml(code_html);
                    var pos = fixed_position(0, 50) + ";width:90%;left:5%;z-index:"+layer;
                    add_div({
                        id: id,
                        cssclass: "inner_box",
                        style: pos
                    });
                    id$(id).innerHTML = code_html;
                    if (ctl_txt == 'ok') {
                        // compute detail
                        var detail = in_child(id, "follow_up_detail");
                        if (detail) {
                            compute_all_ledger();
                        }


                    }
                    code_html.evalScripts();
                } catch (e) {
                    alert_box('view_action' + e.message);
                }
            }
        }
    );
}

/**
 *  filter quickly a table
 * @param  phrase : phrase to seach
 * @param  _id : id of the table
 * @param  colnr : string containing the column number where you're searching separated by a comma
 * @param start_row : first row (1 if you have table header)
 * @returns nothing
 * @see HtmlInput::filter_table
 */
function filter_table(phrase, _id, colnr, start_row) {
    id$('info_div').innerHTML = content[65];
    id$('info_div').style.display = "block";
    var words = id$(phrase).value.toLowerCase();
    var table = document.getElementById(_id);

    // if colnr contains a comma then check several columns
    var aCol = new Array();
    if (colnr.indexOf(',') >= 0) {
        aCol = colnr.split(',');
    } else {
        aCol[0] = colnr;
    }
    var ele;
    var tot_found = 0;

    for (var r = start_row; r < table.rows.length; r++) {
        var found = 0;
        for (var col = 0; col < aCol.length; col++) {
            var idx = aCol[col];
            if (table.rows[r].cells[idx]) {
                ele = table.rows[r].cells[idx].innerHTML.replace(/<[^>]+>/g, "");
                //var displayStyle = 'none';
                if (ele.toLowerCase().indexOf(words) >= 0) {
                    found = 1;
                }
            }

        }
        if (found === 1) {
            tot_found++;
            table.rows[r].style.display = '';
        } else {
            table.rows[r].style.display = 'none';
        }
        id$('info_div').style.display = "none";
        id$('info_div').innerHTML = "";
    }
    if (tot_found == 0) {
        if (document.getElementById('info_' + _id)) {
            id$('info_' + _id).innerHTML = content[69];
            id$('info_' + _id).style.display = 'inline-block';
        }
    } else {
        if (document.getElementById('info_' + _id)) {
            id$('info_' + _id).innerHTML = "  ";
            id$('info_' + _id).style.display = 'none';
        }
    }
    id$('info_div').style.display = "none";
    id$('info_div').innerHTML = "";
}

/**
 *  filter quickly a list, the content to check must be inside a SPAN with the CLASS "search-content"
 * @param  phrase : DOM id of the input text where we find the word to seach, the searchable content use the className searchContent
 * @param  _id : id of the list
 * @returns nothing
 * @see HtmlInput::filter_list
 */
function filter_list(phrase, _id) {
    id$('info_div').innerHTML = content[65];
    id$('info_div').style.display = "block";
    var words = id$(phrase).value.toLowerCase();
    var l_list = document.getElementById(_id);


    var tot_found = 0;

    for (var r = 0; r < l_list.childNodes.length; r++) {
        var found = 0;

        if (l_list.childNodes[r].nodeType != 1) {
            continue;
        }
        let ele = "";
        let la_content = l_list.childNodes[r].getElementsByClassName("search-content");

        let e = 0;
        for (e = 0; e < la_content.length; e++) {
            ele += la_content[e].innerText;
        }

        console.debug(`ele = ${ele}`);
        if (ele.toLowerCase().indexOf(words) >= 0) {
            tot_found++;
            l_list.childNodes[r].style.display = 'block';
        } else {
            l_list.childNodes[r].style.display = 'none';
        }

    }
    if (tot_found == 0) {
        if (document.getElementById('info_' + _id)) {
            id$('info_' + _id).innerHTML = content[69];
        }
    } else {
        if (document.getElementById('info_' + _id)) {
            id$('info_' + _id).innerHTML = "  ";
        }
    }
    id$('info_div').style.display = "none";
    id$('info_div').innerHTML = "";
}

/**
 *  filter quickly a select
 * @param  phrase : DOM id of the input text where we find the word to seach
 * @param  _id : id of the list
 * @returns nothing
 * @see HtmlInput::filter_list
 */
function filter_multiselect(phrase, _id) {
    id$('info_div').innerHTML = content[65];
    id$('info_div').style.display = "block";
    var words = id$(phrase).value.toLowerCase();
    var l_list = document.getElementById(_id);

    var tot_found = 0;

    for (var r = 0; r < l_list.options.length; r++) {
        var found = 0;
        var ele = l_list.options[r].text;

        if (ele.toLowerCase().indexOf(words) >= 0) {
            tot_found++;
            l_list.options[r].style.display = 'block';
        } else {
            l_list.options[r].style.display = 'none';
        }
        id$('info_div').style.display = "none";
        id$('info_div').innerHTML = "";
    }
    if (tot_found == 0) {
        if (document.getElementById('info_' + _id)) {
            id$('info_' + _id).innerHTML = content[69];
        }
    } else {
        if (document.getElementById('info_' + _id)) {
            id$('info_' + _id).innerHTML = "  ";
        }
    }
}

/**
 *
 * Display the task late or for today in dashboard
 */
function display_task(p_id) {

    id$(p_id).style.top = posY + 'px';
    id$(p_id).style.left = "10%";
    id$(p_id).style.width = "80%";
    id$(p_id).style.display = 'block';

}

/**
 *
 * Set a message in the info
 */
function info_message(p_message) {
    id$('info_div').innerHTML = p_message;
    id$('info_div').style.display = "block";
}

/**
 *  hide the info box
 */
function info_hide() {
    id$('info_div').style.display = "none";
}

/**
 * Show the navigator in a internal window
 * @returns {undefined}
 */
function ask_navigator(p_dossier) {
    try {
        waiting_box();
        removeDiv('navi_div')
        var queryString = "gDossier=" + p_dossier + "&op=navigator";
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get', parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req) {
                    remove_waiting_box();
                    add_div({id: 'navi_div', style: 'top:2em;', cssclass: 'inner_box'});
                    id$('navi_div').innerHTML = req.responseText;
                    try {
                        req.responseText.evalScripts();
                        sorttable.makeSortable(id$("navi_tb"));
                    } catch (e) {
                        alert_box("answer_box Impossible executer script de la reponse\n" + e.message);
                    }

                }
            }
        );
    } catch (e) {
        info_message(e.message);
    }

}

/**
 *  Display an internal windows to set the user's preference
 *
 */
function set_preference(p_dossier) {
    try {
        waiting_box();
        removeDiv('preference_div')
        var queryString = "gDossier=" + p_dossier + "&op=preference";
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get', parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req) {
                    remove_waiting_box();
                    if (req.responseText === 'NOCONX') {
                        reconnect();
                        return;
                    }
                    add_div({id: 'preference_div', drag: 1});
                    id$('preference_div').innerHTML = req.responseText;
                    try {
                        req.responseText.evalScripts();
                    } catch (e) {
                        alert_box("answer_box Impossible executer script de la reponse\n" + e.message);
                    }

                }
            }
        );
    } catch (e) {
        info_message(e.message);
    }

}
////////////////////////////////////////////////////////
/**
 *@class Bookmark
 */

////////////////////////////////////////////////////////
var Bookmark = function() {

}
/**
 *  Display user's bookmark
 * @param p_dossier {int} Dossier id
 */
 Bookmark.prototype.show = function (p_dossier) {
    try {
        waiting_box();
        removeDiv('bookmark_div');
        var param = window.location.search;
        param = param.gsub('?', '');
        var queryString = "gDossier=" + p_dossier + "&op=bookmark&" + param;
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get', parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req) {
                    remove_waiting_box();
                    add_div({id: 'bookmark_div', cssclass: 'inner_box', drag: 1});
                    id$('bookmark_div').innerHTML = req.responseText;
                    try {
                        req.responseText.evalScripts();
                    } catch (e) {
                        alert_box(content[53] + "\n" + e.message);
                    }
                    id$('lk_bookmark_tb').focus();

                }
            }
        );
    } catch (e) {
        info_message(e.message);
    }

}
/**
 *  save the bookmark
 */
Bookmark.prototype.save = function () {
    try {
        waiting_box();
        var queryString = "op=bookmark&" + id$("bookmark_frm").serialize();
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get', parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req) {
                    remove_waiting_box();
                    // removeDiv('bookmark_div');
                    //
                    id$('bookmark_div').innerHTML = req.responseText;
                    try {
                        req.responseText.evalScripts();
                    } catch (e) {
                        alert_box(content[53] + "\n" + e.message);
                    }

                }
            }
        );
    } catch (e) {
        info_message(e.message);
    }

}

/**
 *  remove selected bookmark
 */
Bookmark.prototype.remove= function () {
    try {
        waiting_box();
        var queryString = "op=bookmark&" + id$("bookmark_del_frm").serialize();
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get', parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req) {
                    remove_waiting_box();
                    id$('bookmark_div').innerHTML = req.responseText;
                    try {
                        req.responseText.evalScripts();
                    } catch (e) {
                        alert_box(content[53] + "\n" + e.message);
                    }

                }
            }
        );
    } catch (e) {
        error_message(e.message);
    }

}

/**
 * display the error message into the div error_content_div (included into error_div)
 *@param message message to display
 *@note there is no protection
 */
function error_message(message) {
    id$('error_content_div').innerHTML = message;
    id$('error_div').style.visibility = 'visible';
}

/**
 *  show the detail of a tag and propose to save it
 */
function show_tag(p_dossier, p_ac, p_tag_id, p_post) {
    try {
        waiting_box();
        var queryString = "op=tag_detail&tag=" + p_tag_id + "&gDossier=" + p_dossier + "&ac=" + p_ac + '&form=' + p_post;
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get', parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req) {
                    var answer = req.responseXML;
                    var html = answer.getElementsByTagName('code');
                    if (html.length === 0) {
                        var rec = req.responseText;
                        alert_box('erreur :' + rec);
                    }
                    var code_html = getNodeText(html[0]);
                    code_html = unescape_xml(code_html);
                    remove_waiting_box();
                    var posy = calcy(250);
                    add_div({id: 'tag_div', cssclass: 'inner_box', drag: 0, style: "position:fixed;top:15%;"});
                    id$('tag_div').innerHTML = code_html;
                    try {
                        code_html.evalScripts();
                    } catch (e) {
                        alert_box(content[53] + "\n" + e.message);
                    }

                }
            }
        );
    } catch (e) {
        error_message(e.message);
    }
}

/**
 *  save the modified tag
 */
function save_tag() {
    try {
        waiting_box();
        var queryString = "op=tag_save&" + id$("tag_detail_frm").serialize();
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get',
                parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req, j) {
                    remove_waiting_box();
                    removeDiv('tag_div');
                }
            }
        );
    } catch (e) {
        error_message(e.message);
        return false;
    }
    return false;

}

/**
 * Show a list of tag which can be added to the current followup document
 * @param {type} p_dossier
 * @param {type} ag_id
 * @returns {undefined}
 */
function action_tag_select(p_dossier, ag_id) {
    try {
        waiting_box();
        var queryString = "ag_id=" + ag_id + "&op=tag_list&gDossier=" + p_dossier;
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get', parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req, j) {
                    var answer = req.responseXML;
                    var html = answer.getElementsByTagName('code');
                    if (html.length === 0) {
                        var rec = unescape_xml(req.responseText);
                        error_message('erreur :' + rec);
                    }
                    var code_html = getNodeText(html[0]);
                    code_html = unescape_xml(code_html);
                    var pos = fixed_position(35, 229);
                    add_div({id: 'tag_div', style: pos, cssclass: 'inner_box tag', drag: 0});

                    remove_waiting_box();
                    id$('tag_div').innerHTML = code_html;
                }
            }
        );
    } catch (e) {
        error_message(e.message);
    }
}

/**
 *  Add the current tag to the current ag_id
 * @param {type} p_dossier
 * @param {type} ag_id
 * @param p_isgroup g it is a group , t is a single tag
 * @returns {undefined}
 */
function action_tag_add(p_dossier, ag_id, t_id, p_isgroup) {
    try {
        waiting_box();
        var queryString = "t_id=" + t_id + "&ag_id=" + ag_id + "&op=tag_add&gDossier=" + p_dossier + "&isgroup=" + p_isgroup;
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get', parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req, j) {
                    var answer = req.responseXML;
                    var html = answer.getElementsByTagName('code');
                    if (html.length === 0) {
                        var rec = unescape_xml(req.responseText);
                        error_message('erreur :' + rec);
                    }
                    var code_html = getNodeText(html[0]);
                    code_html = unescape_xml(code_html);
                    remove_waiting_box();
                    id$('action_tag_td').innerHTML = code_html;
                    removeDiv('tag_div');
                }
            }
        );
    } catch (e) {
        error_message(e.message);
    }
}

/**
 *  remove the current tag to the current ag_id
 * @param {type} p_dossier
 * @param {type} ag_id
 * @returns {undefined}
 */
function action_tag_remove(p_dossier, ag_id, t_id) {
    confirm_box(null, content[50], function () {
        try {
            waiting_box();
            var queryString = "t_id=" + t_id + "&ag_id=" + ag_id + "&op=tag_remove&gDossier=" + p_dossier;
            var action = new Ajax.Request(
                "ajax_misc.php",
                {
                    method: 'get', parameters: queryString,
                    onFailure: ajax_misc_failure,
                    onSuccess: function (req) {
                        var answer = req.responseXML;
                        var html = answer.getElementsByTagName('code');
                        if (html.length === 0) {
                            var rec = unescape_xml(req.responseText);
                            error_message('erreur :' + rec);
                        }
                        var code_html = getNodeText(html[0]);
                        code_html = unescape_xml(code_html);
                        remove_waiting_box();
                        id$('action_tag_td').innerHTML = code_html;

                    }
                }
            );
        } catch (e) {
            error_message(e.message);
        }
    });
}

/**
 * Activate a tag
 * @param int p_dossier
 * @param int  p_tag_id
 */
function activate_tag(p_dossier, p_tag_id) {
    waiting_box();
    new Ajax.Request("ajax_misc.php",
        {
            method: "get",
            parameters: {gDossier: p_dossier, op: 'tag_activate', t_id: p_tag_id},
            onSuccess: function (req) {
                remove_waiting_box();
                var answer = req.responseText.evalJSON();
                var tagId = "tag_onoff" + p_tag_id;
                id$(tagId).update(answer.code);
                id$(tagId).setStyle(answer.style);
                remove_waiting_box();
            }
        })
}

/**
 * Display a div with available tags, this div can update the cell
 * tag_choose_td
 * @param {type} p_dossier
 * @param {string} p_prefix is the prefix of the div
 * @param {string} Calling object either Tag_Operation or Tag_Action
 * @returns {undefined}
 *
 */
function search_display_tag(p_dossier, p_prefix, p_object) {
    try {
        waiting_box();
        var queryString = {op: "search_display_tag", gDossier: p_dossier, pref: p_prefix, caller_obj: p_object};
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get', parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req, j) {
                    var answer = req.responseXML;
                    var html = answer.getElementsByTagName('code');
                    if (html.length === 0) {
                        var rec = unescape_xml(req.responseText);
                        error_message('erreur :' + rec);
                    }
                    var code_html = getNodeText(html[0]);
                    code_html = unescape_xml(code_html);
                    remove_waiting_box();
                    add_div({id: p_prefix + 'tag_div', style: 'left:10%;width:70%', cssclass: 'inner_box', drag: 1});
                    id$(p_prefix + 'tag_div').style.top = calcy(200) + "px"
                    id$(p_prefix + 'tag_div').style.left = 20 + "%";
                    remove_waiting_box();
                    id$(p_prefix + 'tag_div').innerHTML = code_html;
                    code_html.evalScripts();
                }
            }
        );
    } catch (e) {
        error_message(e.message);
    }
}

/**
 *  Add the selected tag (p_tag_id) to the cell of tag_choose_td in the search screen
 * in the search screen
 * @param {type} p_dossier
 * @param {type} p_tag_id
 * @param p_prefix is the prefix of the widget
 * @param p_obj is either g for group of tag or t for a single tag
 */
function search_add_tag(p_dossier, p_tag_id, p_prefix, p_obj) {
    try {
        var clear_button = 0;
        if (tag_choose === '' && p_prefix === 'search') {
            tag_choose = id$(p_prefix + 'tag_choose_td').innerHTML;
            clear_button = 1;
        }
        waiting_box();
        var queryString = "op=search_add_tag&gDossier=" + p_dossier + "&id=" + p_tag_id + "&clear=" + clear_button + '&pref=' + p_prefix + "&obj=" + p_obj;
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get', parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req, j) {
                    var answer = req.responseXML;
                    var html = answer.getElementsByTagName('html');
                    if (html.length === 0) {
                        var rec = unescape_xml(req.responseText);
                        error_message('erreur :' + rec);
                    }
                    var code_html = getNodeText(html[0]);
                    code_html = unescape_xml(code_html);
                    remove_waiting_box();
                    id$(p_prefix + 'tag_choose_td').innerHTML = id$(p_prefix + 'tag_choose_td').innerHTML + code_html;
                    removeDiv(p_prefix + 'tag_div');
                }
            }
        );
    } catch (e) {
        error_message(e.message);
    }
}

/**
 * Clear the tags in the cell tag_choose_td of the search screen
 * @returns {undefined}
 */
function search_clear_tag(p_dossier, p_prefix) {
    if (p_prefix != 'search') {
        id$(p_prefix + 'tag_choose_td').innerHTML = "";
        return;
    }
    try {
        var queryString = "op=search_clear_tag&gDossier=" + p_dossier + "&pref=" + p_prefix;
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get', parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req, j) {
                    var answer = req.responseXML;
                    var html = answer.getElementsByTagName('html');
                    if (html.length === 0) {
                        var rec = unescape_xml(req.responseText);
                        error_message('erreur :' + rec);
                    }
                    var code_html = getNodeText(html[0]);
                    code_html = unescape_xml(code_html);
                    id$(p_prefix + 'tag_choose_td').innerHTML = code_html;
                    tag_choose = "";
                }
            }
        );
    } catch (e) {
        error_message(e.message);
    }
}

function action_show_checkbox() {
    var a = document.getElementsByName('ag_id_td');
    for (var i = 0; i < a.length; i++) {
        a[i].style.display = 'block';
    }
}

function action_hide_checkbox() {
    var a = document.getElementsByName('ag_id_td');
    for (var i = 0; i < a.length; i++) {
        a[i].style.display = 'none';
    }
}

/**
 *
 * @param {type} obj
 * object attribute : g
 *   - Dossier dossier_id,
 *   - invalue DOM Element where you can find the periode to zoom
 *   - outdiv  ID of the target (DIV)
 *
 */
function calendar_zoom(obj) {
    try {
        waiting_box();
        var per_periode = null;
        var notitle = 0;
        var from = 0;
        if (id$(obj.invalue)) {
            per_periode = id$(obj.invalue).value;
        }
        if (obj.notitle && obj.notitle == 1) {
            notitle = 1;
        }
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get',
                parameters: {
                    "notitle": notitle,
                    "op": 'calendar_zoom',
                    'from': from,
                    'gDossier': obj.gDossier,
                    'in': per_periode,
                    'out': obj.outdiv,
                    'distype': obj.distype
                },
                onFailure: ajax_misc_failure,
                onSuccess: function (req, j) {
                    if (req.responseText === 'NOCONX') {
                        reconnect();
                        return;
                    }
                    var answer = req.responseXML;
                    var html = answer.getElementsByTagName('html');
                    if (html.length === 0) {
                        var rec = unescape_xml(req.responseText);
                        error_message('erreur :' + rec);
                    }
                    var code_html = getNodeText(html[0]);
                    code_html = unescape_xml(code_html);

                    // if the target doesn't exist
                    // then create it
                    if (obj.outdiv === undefined) {
                        obj.outdiv = 'calendar_zoom_div';
                    }
                    if (id$(obj.outdiv) == undefined) {
                        var str_style = 'top:10%;min-height:60rem';
//                            var str_style = fixed_position(0, 120);
                        add_div({
                            id: obj.outdiv,
                            style: 'width:94%;' + str_style,
                            cssclass: "inner_box",
                            drag: 0
                        });
                    }
                    remove_waiting_box();
                    id$(obj.outdiv).innerHTML = code_html;
                    id$(obj.outdiv).show();
                }
            }
        );
    } catch (e) {
        error_message('calendar_zoom ' + e.message);
    }


}

/**
 *  add a line in the form for the stock
 */
function stock_add_row() {
    try {
        style = 'class="input_text"';
        var mytable = id$("stock_tb").tBodies[0];
        var ofirstRow = mytable.rows[1];
        var line = mytable.rows.length;
        var nCell = mytable.rows[1].cells.length;
        var row = mytable.insertRow(line);
        var nb = id$("row");
        for (var e = 0; e < nCell; e++) {
            var newCell = row.insertCell(e);
            if (mytable.rows[1].cells[e].hasClassName('num')) {
                newCell.addClassName("num");
            }

            var tt = ofirstRow.cells[e].innerHTML;
            var new_tt = tt.replace(/sg_code0/g, "sg_code" + nb.value);
            new_tt = new_tt.replace(/sg_quantity0/g, "sg_quantity" + nb.value);
            new_tt = new_tt.replace(/label0/g, "label" + nb.value);
            newCell.innerHTML = new_tt;
            new_tt.evalScripts();
        }

        id$("sg_code" + nb.value).innerHTML = '&nbsp;';
        id$("sg_code" + nb.value).value = '';
        id$("label" + nb.value).innerHTML = '';
        id$("sg_quantity" + nb.value).value = '0';

        nb.value++;

        new_tt.evalScripts();
    } catch (e) {
        alert_box(e.message);
    }

}

function show_description(p_id) {
    id$('print_desc' + p_id).hide();
    id$('input_desc' + p_id).show();

}

/**
 * Display an empty card to fill , with the right card category
 * @param pn_fiche_card_id : fiche_def.fd_id
 * @param pn_dossier_id
 */
function select_cat(pn_fiche_card_id, pn_dossier_id, ps_element_id) {
    dis_blank_card({
        "ctl": "div_new_card",
        "fd_id": pn_fiche_card_id,
        "op2": "bc",
        "op": "card",
        gDossier: pn_dossier_id,
        "elementId": ps_element_id
    });
    removeDiv('select_card_div');
}

/**
 * Show the DIV and hide the other, the array of possible DIV are
 * in a_tabs,
 * @param {array} a_tabs name of possible tabs
 * @param {strng} p_display_tab tab to display
 */
function show_tabs(a_tabs, p_display_tab) {
    try {
        if (a_tabs.length == 0) {
            console.error('a_tabs in empty');
            throw ("a_tabs empty");
            return;
        }
        var i = 0;
        for (i = 0; i < a_tabs.length; i++) {
            id$(a_tabs[i]).hide();
        }
        id$(p_display_tab).show();
    } catch (e) {
        alert_box(e.message);
    }

}

/**
 * Change the class of all the "LI" element of a UL or OL
 * @param node of ul (this)
 */
function unselect_other_tab(p_tab) {
    try {
        var other = p_tab.getElementsByTagName("li");
        var i = 0;
        var tab = null;
        for (i = 0; i < other.length; i++) {
            tab = other[i];
            tab.className = "tabs";
        }
    } catch (e) {
        if (console)
            console.error(e.message);
        alert_box('unselect_other_tab ' + e.message);
    }
}

/**
 * logout function call from ajax
 * @see ajax_disconnected
 * @returns {undefined}
 */
function logout() {
    var tmp_place = window.location.href
    var tmp_b = tmp_place.split('/')
    var tmp_last = tmp_b.length - 1
    var place_logout = tmp_place.replace(tmp_b[tmp_last], 'logout.php');
    window.location.href = place_logout;
}

/**
 * Create a div which can be used in a anchor
 * @returns {undefined}
 */
function create_anchor_up() {
    if (document.getElementById('up_top'))
        return;

    var newElt = document.createElement('div');
    newElt.setAttribute('id', 'up_top');
    newElt.innerHTML = '<a id="up_top"></a>';

    var parent = id$('info_div').parentNode;
    parent.insertBefore(newElt, id$('info_div'));

}

/**
 * Initialize the window to show the button "UP" if the window is scrolled
 * vertically
 * @returns {undefined}
 */
function init_scroll() {
    var up = new Element('div', {
        "class": "",
        "style": "padding:5px;left:auto;width:auto;height: auto;display:none;position:fixed;bottom:30%;right:50px;text-align:center;font-size:20px",
        id: "go_up"
    });
    up.innerHTML = '<a class="icon" onclick="document.getElementById(\'go_up\').hide()" style="float:right;font-size:70%">&#xe816;</a> <a class="icon" href="#up_top" >&#xe81a;</a><a href="javascript:show_calc()" class="icon">&#xf1ec;</a>';
    document.body.appendChild(up);
    window.onscroll = function () {
        if (document.getElementById("select_box_content")) {
            document.getElementById("select_box_content").setStyle({display: "none"})
        }
        ;
        if (document.viewport.getScrollOffsets().top > 0) {
            if (id$('go_up').visible() == false) {
                id$('go_up').setOpacity(0.65);
                id$('go_up').show();
                id$('go_up').style.zIndex = 99;
            }
        } else {
            id$('go_up').hide();
        }
    }

}
function loading_page() {
    var id_page = new Element('div', {
        "class": "",
        "style": "padding: 5px;\n" +
            "  width: 300px;\n" +
            "  height: 60px;\n" +
            "  display: block;\n" +
            "  position: fixed;\n" +
            "  bottom: 50px;\n" +
            "  left: 50px;\n" +
            "  text-align: center;\n" +
            "  animation-name: fill_up_loading;\n" +
            "  animation-duration: 8s;\n" +
            "  animation-iteration-count: infinite;"+
            "opacity: 0.7;"+
            "border-radius: 5px;"+
            "font-size: 300%;"+
        "animation-timing-function: linear;",
        id: "loading_page_div"
    });
    id_page.update('<div class="loading_msg"></div><div class="loading_msg"></div><div class="loading_msg"></div><div class="loading_msg"></div><div class="loading_msg"></div>');
    document.body.appendChild(id_page);
}
/**
 * Confirm a form thanks a modal dialog Box, it returns true if we agree otherwise
 * false
 * @code
 <form onsubmit="return confirm_box(this,'message')">
 </form>
 * @endcode
 * @param p_obj form element (object) or element id (string)
 * @param p_message message to display
 * @param p_callback_true  callback function or null
 * @param p_waiting if true display a waiting box
 * @returns true or false
 */
function confirm_box(p_obj, p_message, p_callback_true, p_waiting) {
    waiting_box();
    try {
        // Find id of the end
        var name = "";
        if (p_obj != null) {
            if (typeof (p_obj) === "object") {
                name = p_obj.id;
            } else {
                name = p_obj;
            }
        }

        // execute the callback function or submit the form
        if (!p_callback_true) {

            smoke.confirm(p_message, function (e) {
                if (e) {
                    if (p_waiting) {
                        waiting_box();
                    }
                    id$(name).submit();
                }
            });
        } else {
            smoke.confirm(p_message, function (e) {
                if (e) {
                    p_callback_true.apply();
                }
            });
        }
    } catch (e) {
        alert_box(e.message);
    }
    remove_waiting_box();
    return false;
}

/**
 * Alert box in CSS and HTML to replace the common javascript alert
 * @param p_message message to display
 * @returns void
 */
function alert_box(p_message) {
    smoke.alert(p_message, undefined, {ok: 'ok', classname: "inner_box",title:'ATTENTION'});
}


/**
 *  Colorize the rows of the table
 * @param string p_table id of the table
 */
function alternate_row_color(p_table) {
    var table_colored = id$(p_table);
    if (!table_colored.tBodies[0]) return;

    var len = table_colored.tBodies[0].rows.length;
    var i = 0;
    var localClass = "";
    for (i = 1; i < len; i++) {
        localClass = (i % 2 == 0) ? "even" : "odd";
        if (table_colored.tBodies[0].rows[i].hasClassName("odd")) {
            table_colored.tBodies[0].rows[i].removeClassName("odd");
        }
        if (table_colored.tBodies[0].rows[i].hasClassName("even")) {
            table_colored.tBodies[0].rows[i].removeClassName("even");
        }
        table_colored.tBodies[0].rows[i].addClassName(localClass);
    }

}
/**
 *  Colorize the rows of the list
 * @param p_list {string} DOM id of the list
 */
function alternate_row_color_list(p_list) {
    var list_colored = id$(p_list);
    if ( list_colored.children.length==0 ) return;

    var len = list_colored.children.length;
    var i = 0;
    var localClass = "";
    for (i = 1; i < len; i++) {
        localClass = (i % 2 == 0) ? "even" : "odd";
        if (list_colored.children[i].hasClassName("odd")) {
            list_colored.children[i].removeClassName("odd");
        }
        if (list_colored.children[i].hasClassName("even")) {
            list_colored.children[i].removeClassName("even");
        }
        list_colored.children[i].addClassName(localClass);
    }

}


/**
 * Make an DOM element draggable or not
 * @param object_id DOM id
 */
function pin(object_id) {
    if (aDraggableElement[object_id]) {
        aDraggableElement[object_id].destroy();
        aDraggableElement[object_id] = undefined;
        id$('pin_' + object_id).innerHTML = "&#xf047;";
    } else {
        aDraggableElement[object_id] = new Draggable(object_id, {
                starteffect: function () {
                    new Effect.Highlight(object_id, {scroll: window, queue: 'end'});
                }
            }
        );
        id$('pin_' + object_id).innerHTML = "&#xe809;";
    }
}

/**
 * Show only the rows into the table (p_table_id) with the attribute (p_attribute_name) and if this attribute
 * has the value of  (attribut_value)
 * @param p_table_id table id
 * @param p_attribute_name the name of the attribute
 * @param p_attribute_value the value of the attribute we want to show
 */
function show_only_row(p_table_id, p_attribute_name, p_attribute_value) {
    if (!id$(p_table_id)) {
        throw "Invalide table id"
    }
    var mTable = id$(p_table_id);
    var ncount = mTable.rows.length
    for (var i = 0; i < ncount; i++) {
        var mRow = mTable.rows[i];
        if (mRow.getAttribute(p_attribute_name) != undefined && mRow.getAttribute(p_attribute_name) != p_attribute_value) {
            mRow.hide();
        } else {
            mRow.show();
        }
    }
}

/**
 * Show all the rows into the table (p_table_id)
 * @param p_table_id table id
 */
function show_all_row(p_table_id) {
    if (!id$(p_table_id)) {
        throw "Invalide table id"
    }
    var mTable = id$(p_table_id);
    var ncount = mTable.rows.length
    for (var i = 0; i < ncount; i++) {
        var mRow = mTable.rows[i];
        mRow.show();
    }

}

/**
 * @class
 * Periode handling
 * Variables :
 * id of the row of the periode row_per_(p_periode_id) , attribute exercice =per_exercice,periode_id=p_id
 *    # (this.dialog)
 *    # id of the table with the rows : periode_tbl
 *
 * Members :
 *   - periode_id the concerned Periode , 0 none
 *   - p_ledger : the id of ledger (jrn_def.jrn_def_id), 0 for global
 *   - pcallback : default ajax_misc.php (this.callback) with the parameter { op:'periode',gDossier,[action:display,remove,save],p_id:p_periode_id}
 *   - dossier
 *   - js_obj_name : name of the js object (this.js_obj_name)
 *   - ajax_test : file to include for debugging
 *   - dialog : id of the dialog box (update / add ) periode_box
 *
 */
var Periode = function (p_ledger) {
    this.periode_id = 0;
    this.p_ledger = p_ledger;
    this.dialog = 'periode_box';
    this.pcallback = 'ajax_misc.php';
    this.dossier = 0;
    this.js_obj_name = "";
    this.ajax_test = "";
    this.set_callback = function (p_phpfile) {
        this.pcallback = p_phpfile;
    };
    this.set_dossier = function (p_dosid) {
        this.dossier = p_dosid;
    };
    /**
     * set_js_obj_name (p_js_obj_name)
     * We need to know the javascript variable name , to pass it to ajax and
     * create a HTML containing the right variable
     * @param  p_js_obj_name name of the variable js we use on caller side
     */
    this.set_js_obj_name = function (p_js_obj_name) {
        this.js_obj_name = p_js_obj_name;
    };

    /**
     * Remove the periode , so call new Ajax and hide the row if successful
     * otherwise show dialog box.
     * @parameter p_periode_id is the id of periode
     */
    this.remove = function (p_periode_id) {

        var js_param = {
            "gDossier": this.dossier,
            "op": "periode",
            "act": "remove",
            "p_id": p_periode_id,
            "ledger_id": 0,
            "js_var": this.js_obj_name
        };
        if (this.ajax_test != "") {
            js_param["TestAjaxFile"] = this.ajax_test;
        }
        here = this;
        smoke.confirm("Confirmer  ?", function (e) {
            if (e) {
                waiting_box();
                new Ajax.Request(here.pcallback,
                    {
                        method: "POST",
                        parameters: js_param,
                        onSuccess: function (req) {
                            var answer = req.responseText.evalJSON();
                            remove_waiting_box();
                            if (answer.status == "OK") {
                                id$("row_per_" + p_periode_id).remove();
                                alternate_row_color("periode_tbl");
                            } else {
                                smoke.alert(answer.content);
                            }
                        }
                    });
            }
        });
    };

    /**
     * display a dialog box to update a periode, call save either display
     * an error box or update the row.
     * the name of variable is requested
     * to build the right button , javascript in the html of answer
     * @parameter p_periode_id is the id of periode
     */
    this.box_display = function (p_periode_id) {
        if (this.js_obj_name == "") {
            smoke.alert("ERROR BOX_ADD")
        }

        var js_param = {
            "gDossier": this.dossier,
            "op": "periode",
            "act": "show",
            "p_id": p_periode_id,
            "ledger_id": this.p_ledger,
            "js_var": this.js_obj_name
        };
        if (this.ajax_test != "") {
            js_param["TestAjaxFile"] = this.ajax_test;
        }
        var here = this;
        new Ajax.Request(here.pcallback,
            {
                method: "POST",
                parameters: js_param,
                onSuccess: function (req) {
                    remove_waiting_box();
                    var json = req.responseText.evalJSON();
                    var y = 100;
                    add_div({
                        "id": "mod_periode",
                        "style": "position:fixed;top:" + y + "px;width:50%",
                        "cssclass": "inner_box",
                        'html': "wait"
                    });
                    id$('mod_periode').update(json.content);
                }
            });
    };
    /**
     * close the periode, call ajax and receive a json object with the attribute
     * status, content
     * @parameter p_periode_id is the id of periode
     */
    this.close_periode = function (p_periode_id) {
        if (this.js_obj_name == "") {
            smoke.alert("ERROR BOX_ADD")
        }

        if (this.ajax_test != "") {
            js_param["TestAjaxFile"] = this.ajax_test;
        }
        var here = this;
        smoke.confirm("Confirmer  ?", function (e) {
            if (e) {
                here._close(p_periode_id);
            }
        });
    };
    /**
     * Internal function to close without confirming
     * @param {type} p_periode_id
     * @returns {undefined}
     */
    this._close = function (p_periode_id) {
        if (this.js_obj_name == "") {
            smoke.alert("ERROR BOX_ADD")
        }
        var js_param = {
            "gDossier": this.dossier,
            "op": "periode",
            "act": "close",
            "ledger_id": this.p_ledger,
            "p_id": p_periode_id,
            "js_var": this.js_obj_name
        };
        if (this.ajax_test != "") {
            js_param["TestAjaxFile"] = this.ajax_test;
        }
        var here = this;
        waiting_box();
        new Ajax.Request(here.pcallback,
            {
                method: "POST",
                parameters: js_param,
                onSuccess: function (req) {
                    remove_waiting_box();
                    var json = req.responseText.evalJSON();
                    if (json.status == 'OK') {
                        id$('row_per_' + p_periode_id).update(json.content);
                        new Effect.Highlight('row_per_' + p_periode_id, {startcolor: '#FAD4D4', endcolor: '#F78082'});
                    } else {
                        smoke.alert(json.content);
                    }
                }
            });
    };
    /**
     * reopen the periode
     * @parameter p_periode_id is the SQL id of parm_periode or the id of
     * jrn_periode
     */
    this.open_periode = function (p_periode_id) {
        if (this.js_obj_name == "") {
            smoke.alert("ERROR BOX_ADD")
        }
        var js_param = {
            "gDossier": this.dossier,
            "op": "periode",
            "act": "reopen",
            "ledger_id": this.p_ledger,
            "p_id": p_periode_id,
            "js_var": this.js_obj_name
        };
        if (this.ajax_test != "") {
            js_param["TestAjaxFile"] = this.ajax_test;
        }
        var here = this;
        smoke.confirm("Confirmer  ?", function (e) {
            if (e) {
                waiting_box();
                new Ajax.Request(here.pcallback,
                    {
                        method: "POST",
                        parameters: js_param,
                        onSuccess: function (req) {
                            remove_waiting_box();
                            var json = req.responseText.evalJSON();
                            if (json.status == 'OK') {
                                id$('row_per_' + p_periode_id).update(json.content);
                                new Effect.Highlight('row_per_' + p_periode_id, {
                                    startcolor: '#FAD4D4',
                                    endcolor: '#F78082'
                                });
                            } else {
                                smoke.alert(json.content);
                            }
                        }
                    });
            }
        });
    };
    /**
     * This DOMID of the DIV containing the form is mod_periode
     * @param {type} p_frm
     * @returns {Boolean}
     */
    this.save = function (p_frm) {
        var js_param = id$(p_frm).serialize(true);
        waiting_box();
        js_param["js_var"] = this.js_obj_name;
        js_param["act"] = "save";
        js_param["op"] = "periode";
        var here = this;
        new Ajax.Request(this.pcallback, {
            method: "POST",
            parameters: js_param,
            onSuccess: function (req) {

                var answer = req.responseText.evalJSON();
                remove_waiting_box();
                if (answer.status == "OK") {
                    id$('row_per_' + js_param['periode_id']).update(answer.content);
                    removeDiv('mod_periode');
                    new Effect.Highlight('row_per_' + js_param['periode_id'], {
                        startcolor: '#FAD4D4',
                        endcolor: '#F78082'
                    });
                } else {
                    smoke.alert(answer.content);
                }
            }
        });
        return false;
    };
    /**
     * Thanks the object DOMID sel_per_closed[] the selected periodes are
     * closed
     * @see Periode._close
     */
    this.close_selected = function () {
        var here = this;
        var a_selected = document.getElementsByName('sel_per_close[]');
        var count = 0;
        var i = 0;
        for (i = 0; i < a_selected.length; i++) {
            if (a_selected[i].checked == true) {
                // Close the selected periode
                count++;
            }
        }
        if (count == 0) {
             smoke.signal("Sélectionner au moins une période", function () {
            }, {duration: 1500});
            return;
        }
        smoke.confirm("Confirmer fermeture de " + count + " periode", function (e) {
                if (e) {
                    var a_selected = document.getElementsByName('sel_per_close[]');
                    var i = 0;
                    for (i = 0; i < a_selected.length; i++) {
                        if (a_selected[i].checked == true) {
                            // Close the selected periode
                            here._close(a_selected[i].value);
                        }
                    }
                }
            }
        );
    };
    /**
     *  Insert a periode into the list, always at the bottom !
     * DomId :
     *   # FORM id :insert_periode_frm
     *   # DIV id = periode_add
     *   # table id = periode_tbl
     */
    this.insert_periode = function () {
        var p_frm = 'insert_periode_frm';
        var js_param = id$(p_frm).serialize(true);
        waiting_box();
        js_param["js_var"] = this.js_obj_name;
        js_param["act"] = "insert_periode";
        js_param["op"] = "periode";
        js_param["p_id"] = "-1";
        js_param["ledger_id"] = "0";
        var here = this;
        new Ajax.Request(this.pcallback, {
            method: "POST",
            parameters: js_param,
            onSuccess: function (req) {
                var answer = req.responseText.evalJSON();
                remove_waiting_box();
                if (answer.status == "OK") {
                    var new_row = document.createElement("tr");
                    id$('periode_tbl').append(new_row);
                    new_row.replace(answer.content);

                    // hide the form
                    id$('periode_add').hide();
                    new Effect.Highlight('row_per_' + answer.p_id, {startcolor: '#FAD4D4', endcolor: '#F78082'});
                    alternate_row_color('periode_tbl');
                } else {
                    smoke.alert(answer.content);
                }
            }
        });
        return false;
    }

}
/**
 * Show the periodes from the exercice contained into the id (p_exercice_sel)
 * @param p_table_id DOM ID of the table
 */
Periode.filter_exercice = function (p_table_id) {
    var rows = id$(p_table_id).rows;
    var selected_value = id$('p_exercice_sel').value;
    for (var i = 1; i < rows.length; i++) {
        var exercice = rows[i].getAttribute("per_exercice");
        if (selected_value == -1) {
            rows[i].show();
        } else if (selected_value == exercice) {
            rows[i].show();
        } else {
            rows[i].hide();
        }

    }
};

// keep track of progress bar
var progressBar = [];
// idx of progress bar        
var progressIdx = 0;

/**
 * Start the progress bar
 * @param {string} p_taskid id to monitor
 * @param {int} p_message
 */
function progress_bar_start(p_taskid, p_message) {
    try {
        progressIdx++;
        // block the window

        var message = '<p>' + content[70] + '</p>';
        if (p_message) {
            message = p_message;
        }

        add_div({id: "blocking" + progressIdx, cssclass: "smoke-base smoke-visible "});

        add_div({
            id: "message" + progressIdx,
            cssclass: "inner_box",
            style: "z-index:1000;position:fixed;top:30%;width:40%;left:30%"
        });
        id$("message" + progressIdx).update('<h3>' + content[65] + '</h3>' + message);
        // Create a div
        add_div({id: "progressDiv" + progressIdx, cssclass: "progressbar", html: '<span id="progressValue">0</span>'});
        // Check status every sec.
        progressBar[progressIdx] = setInterval(progress_bar_check.bind(null, progressIdx, p_taskid), 1000);
    } catch (e) {
        console.error(e.message);
    }
}

/**
 * Check every second the status
 * @param {integer} p_idx idx of progressbar
 * @param {string} p_taskid  id to monitor
 */
function progress_bar_check(p_idx, p_taskid) {
    try {

        new Ajax.Request("ajax_misc.php", {
            parameters: {gDossier: 0, task_id: p_taskid, op: "progressBar"},
            method: "get",
            onSuccess: function (req) {
                try {
                    var answer = req.responseText.evalJSON();
                    var progress_div = id$("progressDiv" + progressIdx);
                    var a_child = progress_div.childNodes;
                    var i = 0;
                    for (i = 0; i < a_child.length; i++) {
                        if (a_child[i].id = "progressValue") {
                            var progressValue = a_child[i];
                        }
                    }
                    var progress = parseFloat(progressValue.innerHTML);
                    if (answer.value <= progress) {
                        return;
                    }

                    progressValue.innerHTML = answer.value;
                    progressValue.setStyle("width:" + answer.value + "%");
                    if (answer.value == 100) {
                        clearInterval(progressBar[p_idx]);
                        progressValue.innerHTML = "Success";
                        Effect.BlindUp("progressDiv" + p_idx, {duration: 1.0, scaleContent: false})
                        id$("message" + p_idx).remove();
                        id$("blocking" + p_idx).remove();
                        setTimeout(function () {
                            id$("progressDiv" + progressIdx).remove
                        }, 1100);
                    }
                } catch (e) {
                    clearInterval(progressBar[p_idx]);
                    document.getElementById("progressValue").innerHTML = req.responseText;
                    console.error(e.message);
                }
            }
        });
    } catch (e) {
        clearInterval(progressBar[p_idx]);
        console.error(e.message);
    }
}

/**
 * In the user's setting  box, update the period list with the choosen exercice
 * @param {int} p_dossier
 */
function updatePeriodePreference(p_dossier) {
    waiting_box();
    var exercice = id$('exercice_setting').value;
    new Ajax.Updater('setting_period', "ajax_misc.php", {
        method: "get",
        parameters: {"op": "pref_exercice", "gDossier": p_dossier, "exercice": exercice}
    });
    remove_waiting_box();
}

/**
 * Update the from and to periode list when changing the exercice
 * @param {int} p_dossier
 * @param {string} p_exercice dom id of the exercice (SELECT)
 * @param {type} p_periode_from id of the starting periode
 * @param {type} p_periode_to id of the ending periode
 * @param {type} p_last possible value = 1 to show last date or 0 the first
 */
function updatePeriode(p_dossier, p_exercice, p_periode_from, p_periode_to, p_last) {
    waiting_box();
    var exercice = id$(p_exercice).value;
    new Ajax.Updater(p_periode_from, "ajax_misc.php",
        {
            method: "get",
            parameters: {
                op: "periode_change", "gDossier": p_dossier, "exercice": exercice,
                field: p_periode_from, "type": "from", "last": p_last
            }
        });
    if (p_periode_to && p_last) {
        new Ajax.Updater(p_periode_to, "ajax_misc.php",
            {
                method: "get",
                parameters: {
                    op: "periode_change", "gDossier": p_dossier, "exercice": exercice,
                    field: p_periode_to, "type": "to", "last": p_last
                }
            });
    }
    remove_waiting_box();
}

/**
 *
 * @param {string} p_domid DOM id of the span containing the padlock icon
 * @returns none
 */
function toggle_lock(p_domid) {
    var padlock = document.getElementById(p_domid);
    if (padlock == null) {
        console.error("domid invalid");
    }
    var status = padlock.getAttribute("is_locked");
    if (status == 1) {
        padlock.innerHTML = "&#xe832;";
        padlock.setAttribute("is_locked", 0);
    } else if (status == 0) {
        padlock.innerHTML = "&#xe831;";
        padlock.setAttribute("is_locked", 1);
    } else {
        throw "toggle_lock failed";
    }


}

/**
 *
 * @returns {undefined}
 */
function show_ledger_fin_currency() {
    var ledger = id$('p_jrn').value;
    var dossier = id$('gDossier').value;
    // id$('ledger_currency').
    var a = new Ajax.Updater("ledger_currency",
        "ajax_misc.php",
        {
            parameters: {"op": "currencyCode", "gDossier": dossier, "ledger": ledger}
        });
}

/***
 * Update Preference, applied the new CSS
 */
function updatePreference() {
    try {
        waiting_box();
        var param = id$('preference_frm').serialize() + "&op=preference&action=save";

        new Ajax.Request("ajax_misc.php", {
            method: "post",
            parameters: param,
            onSuccess: function (req) {
                var answer = req.responseText.evalJSON();
                // id$('pagestyle').setAttribute('href', style.style);
                if (answer['psw'] == 'NOK') {
                    smoke.alert(answer['msg']);
                } else {
                    removeDiv('preference_div');
                }
            }
        });
    } catch (e) {
        smoke.alert(content[48] + e.message);
    }
    remove_waiting_box();

}

/**
 * turn on or off ,  set an domElement to 1 or 0 and change the icon
 * @param string icon_domid : id of the domElement which must be changed
 * @param string p_value_domid : id of domElement containing 1 or 0
 * @see param_jrn.php
 */
function toggle_onoff(icon_domid, p_value_domid) {
    if (id$(p_value_domid).value == 0) {
        id$(p_value_domid).value = 1;
        id$(icon_domid).innerHTML = '&#xf205;';
        id$(icon_domid).style = 'color:green';
    } else {
        id$(p_value_domid).value = 0;
        id$(icon_domid).innerHTML = '&#xf204;';
        id$(icon_domid).style = 'color:red';
    }
}

/**
 * turn on or off ,  set an domElement to 1 or 0 and change the icon
 * @param string icon_domid : id of the domElement which must be changed
 * @param string p_value_domid : id of domElement containing 1 or 0
 * @see param_jrn.php
 */
function toggle_checkbox_onoff(icon_domid, p_value_domid) {

    if (id$(p_value_domid).value == 0) {
        id$(p_value_domid).value = 1;
        id$(icon_domid).innerHTML = '&#xe741;';
    } else {
        id$(p_value_domid).value = 0;
        id$(icon_domid).innerHTML = '&#xf096;';
    }
}

/**
 * in C0JRN show or hide the row depending if the warning is enable or not
 *
 * @param {type} p_enable
 * @param {type} p_row
 * @returns {undefined}
 */
function toggle_row_warning_enable(p_enable, p_row) {
    var warning = document.getElementsByName('negative_amount')[0].value
    if ( warning == 1) {
        id$(p_row).show();
    } else {
        id$(p_row).hide();
    }
}

/**
 * return a json object which is the merge of the 2 json objects
 * from 2015 : Object.assign(obj1, obj2);
 * @param p_json1 object 1 to merge
 * @param p_json2 object 2 to merge
 * @returns new json object
 */
function json_concat(p_json1, p_json2) {

    var result = {};
    for (var key in p_json1) {
        result[key] = p_json1[key];
    }
    for (var key in p_json2) {
        result[key] = p_json2[key];
    }
    return result;

}


/**
 * this function unchecks other checkbox , it mimics the way a radio behaves
 * @param string p_click is the DOM id of the checkbox you clicked
 * @param string p_name is the name of all the checkbox to uncheck
 */
function uncheck_other(p_click, p_name) {
    var aCheckbox = document.getElementsByName(p_name);
    if (aCheckbox.length == 0) return;
    var i = 0;
    for (i = 0; i < aCheckbox.length; i++) {
        aCheckbox[i].checked = false;
    }
    p_click.checked = true;
}

/**
 * @class operation Tag Manage the tag with operations
 * @returns {undefined}
 */
var operation_tag = function (p_div) {
    this.ctl = p_div;
    /**
     * Show a list of tag which can be added to the current followup document
     * @param {type} p_dossier
     * @param {type} jrn_id
     * @returns {undefined}
     */
    this.select = function (p_dossier, p_jrn_id) {
        try {
            waiting_box();
            var queryString = {jrn_id: p_jrn_id, op: "operation_tag_select", gDossier: p_dossier, ctl: this.ctl};
            var action = new Ajax.Request(
                "ajax_misc.php",
                {
                    method: 'get',
                    parameters: queryString,
                    onFailure: ajax_misc_failure,
                    onSuccess: function (req, j) {
                        remove_waiting_box();

                        var answer = req.responseXML;
                        var html = answer.getElementsByTagName('code');
                        if (html.length === 0) {
                            var rec = unescape_xml(req.responseText);
                            error_message('erreur :' + rec);
                        }
                        var code_html = getNodeText(html[0]);
                        code_html = unescape_xml(code_html);
                        var pos = fixed_position(35, 229);
                        add_div({id: 'tag_div', style: pos, cssclass: 'inner_box tag', drag: 0});

                        remove_waiting_box();
                        id$('tag_div').innerHTML = code_html;
                    }
                }
            );
        } catch (e) {
            error_message(e.message);
        }
    };

    /**
     * Add the current tag to the current ag_id
     * @param {int} p_dossier
     * @param int ag_id
     * @param p_isgroup g it is a group , t is a single tag
     * @returns void
     */
    this.add = function (p_dossier, p_jrn_id, t_id, p_isgroup) {
        try {
            waiting_box();
            var queryString = {
                t_id: t_id, jrn_id: p_jrn_id, op: "operation_tag_add",
                gDossier: p_dossier, ctl: this.ctl, isgroup: p_isgroup
            };
            var ctl = this.ctl;
            var action = new Ajax.Request(
                "ajax_misc.php",
                {
                    method: 'get', parameters: queryString,
                    onFailure: ajax_misc_failure,
                    onSuccess: function (req, j) {
                        var answer = req.responseXML;
                        var html = answer.getElementsByTagName('code');
                        if (html.length === 0) {
                            var rec = unescape_xml(req.responseText);
                            error_message('erreur :' + rec);
                        }
                        var code_html = getNodeText(html[0]);
                        code_html = unescape_xml(code_html);
                        remove_waiting_box();
                        id$('operation_tag_td' + ctl).innerHTML = code_html;
                        removeDiv('tag_div');
                    }
                }
            );
        } catch (e) {
            error_message(e.message);
        }
    };
    /**
     * remove the current tag to the current ag_id
     * @param {int} p_dossier
     * @param {int} ag_id
     * @returns void
     */
    this.remove = function (p_dossier, p_jrn_id, t_id) {
        var ctl = this.ctl;
        confirm_box(null, content[50], function () {
            try {
                waiting_box();
                var queryString = {
                    t_id: t_id,
                    jrn_id: p_jrn_id,
                    op: "operation_tag_remove",
                    gDossier: p_dossier,
                    ctl: ctl
                };
                var action = new Ajax.Request(
                    "ajax_misc.php",
                    {
                        method: 'get',
                        parameters: queryString,
                        onFailure: ajax_misc_failure,
                        onSuccess: function (req, j) {
                            var answer = req.responseXML;
                            var html = answer.getElementsByTagName('code');
                            if (html.length === 0) {
                                var rec = unescape_xml(req.responseText);
                                error_message('erreur :' + rec);
                            }
                            var code_html = getNodeText(html[0]);
                            code_html = unescape_xml(code_html);
                            remove_waiting_box();
                            id$('operation_tag_td' + ctl).innerHTML = code_html;

                        }
                    }
                );
            } catch (e) {
                error_message(e.message);
            }
        });
    };
};

/**
 * Check the sum of size of all the FILES to upload
 * @param p_object the form DOM object,
 * @param p_max_size MAX_FILE_SIZE constant (see config.inc.php or constant.php)
 * @returns true if the sum of filesize is greater than the limit
 */
function check_file_size(p_object, p_max_size) {
    var sum_file = 0;
    for (var i = 0; i < p_object.elements.length; i++) {
        var a = p_object.elements[i];

        if (p_object.elements[i].getAttribute('type') == "file") {
            for (let x = 0; x < p_object.elements[i].files.length; x++) {
                if (p_object.elements[i].files[x]) {

                    sum_file += p_object.elements[i].files[x].size;
                }
            }
        }
    }
    if (sum_file > p_max_size) {
        alert_box(content[78]);
        return false;
    }
    return true;
}

/**
 * Check that the receipt file is not too big
 * @see ajax_ledger.php , ledger_detail_file
 * @param int p_max_size maximum size
 * @param p_info name of the waiting box
 * @returns true if  file size is less than the maximum
 */
function check_receipt_size(p_max_size, p_info) {
    document.getElementById(p_info).style.display = "inline";

    var f = document.getElementById("receipt_id");
    if (f && f.files[0] && f.files[0].size > parseFloat(p_max_size)) {
        document.getElementById("receipt_info_id").innerHTML = content[78];
        document.getElementById(p_info).style.display = "none";
        $('receipt_info_id').addClassName('error');
        return false;
    }
    document.getElementById("receipt_info_id").innerHTML = "";
    $('receipt_info_id').removeClassName('error');
    document.getElementById("form_file").submit();
    return true;
}

/**
 *  toggle size of a div : fullsize or normal
 *
 */
function full_size(p_div) {
    div_dom = document.getElementById(p_div);
    if (!div_dom) return;
    if (div_dom.hasClassName('fullsize')) {
        div_dom.removeClassName('fullsize');
        id$('size_' + p_div).innerHTML = '&#xe80a;';
    } else {
        div_dom.addClassName('fullsize');
        id$('size_' + p_div).innerHTML = '&#xe83d;';
    }

}

/**
 *  download a document from an url
 */
function download_document(p_url) {
    waiting_box();
    document.location = p_url;
    remove_waiting_box();
}

/**
 *  download a document from a form
 */
function download_document_form(p_form_id) {
    waiting_box();
    var url = "export.php?" + id$(p_form_id).serialize();
    document.location = url;
    remove_waiting_box();
    return false;
}

/**
 *  Pause a javascript
 */
function pausecomp(millis) {
    var date = new Date();
    var curDate = null;
    do {
        curDate = new Date();
    }
    while (curDate - date < millis);
}

/**
 *  propose to reconnect
 * @returns {undefined}
 */
function reconnect() {
    remove_waiting_box();
    new Ajax.Request('ajax_misc.php', {
        method: 'get',
        parameters: {op: "disconnect"},
        onSuccess: function (req) {
            var pos = "position:fixed;top:0px;width:95%;height:95%";
            var div = add_div({
                'id': "reconnect_bx",
                cssclass: "inner_box",
                style: pos
            });
            div.innerHTML = req.responseText;
        }
    });
}

/**
 *  enlarge an INPUT TEXT
 *
 */
function enlarge_text(p_domid, p_size) {
    try {
        var element = document.getElementById(p_domid);
        if (!element) {
            console.error(`enlarge text doesn't exist [${p_domid}]`)
        }
        var current_size = parseInt(element.getAttribute('size'));
        element.setAttribute('size', current_size + parseInt(p_size));
    } catch (e) {
        console.error(`enlarge text fails with ${p_domid} ${p_size} `);
        console.error(e.message);
    }


}

/**
 * @brief display a box with the customer , supplier or event for today or late
 * @param p_detail , what to do
 */
function event_display_detail(p_dossier, p_detail) {

    try {
        // create div if not exists
        var dgbox = "situation_detail_div";
        waiting_box();

        var queryString = {gDossier: p_dossier, op: 'event_display_detail', 'what': p_detail};
        // call ajax and update content of the div
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get',
                parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req) {
                    remove_waiting_box();
                    if (req.responseText == 'NOCONX') {
                        reconnect();
                        return;
                    }
                    if (!document.getElementById(dgbox)) {
                        var div_style = "position:fixed;" + ";top:30%";
                        add_div({id: dgbox, cssclass: 'inner_box', html: loading(), style: div_style, drag: true});

                    }

                    id$(dgbox).update(req.responseText)

                }
            }
        );
        event_display_main(p_dossier);
    } catch (e) {
        alert_box(e.message);
    }
}

/**
 * @brief refresh the main display in the dashboard to reflect possible changes
 * @param p_dossier
 */
function event_display_main(p_dossier) {
    try {
        waiting_box();
        var dgbox = "situation_div";
        var queryString = {gDossier: p_dossier, op: 'event_display_detail', 'what': "main_display"};
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'GET',
                parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req) {
                    remove_waiting_box();
                    if (req.responseText == 'NOCONX') {
                        reconnect();
                        return;
                    }

                    id$(dgbox).update(req.responseText)

                }
            }
        );
    } catch (e) {
        alert_box(e.message);
    }
}

/**
 * @brief check if password is strong or not, update a DIV element
 * @param p_pass_domid DOM ID of the INPUT element with the password
 * @param p_result_domid DOM ID of the element to update
 */
function check_password_strength(p_pass_domid, p_result_domid, details) {
    try {
        if (id$(p_pass_domid).value == "") {
            id$(p_result_domid).update("");
            return;
        }
        var queryString = {
            'op': "password_chk"
            , pass: id$(p_pass_domid).value
        };
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'GET',
                parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req) {
                    remove_waiting_box();
                    if (req.responseText == 'NOCONX') {
                        return;
                    }
                    var answer = req.responseJSON;
                    console.debug(answer);
                    if (answer['password'] == 'nok') {

                        id$(p_pass_domid).setStyle("background-color:red");
                        if (details) {
                            id$(p_result_domid).update(answer['msg'])
                        }
                        return;
                    }
                    id$(p_pass_domid).setStyle("background-color: lightgreen");
                    id$(p_result_domid).update("")
                }
            }
        );
    } catch (e) {
        alert_box(e.message);
    }
}

/**
 * activate a plugin , must comes from C0PLG
 * @param elt {string}  DOMID of the element, must have the attribute gDossier, plugin and pr_id (for the profile)
 * @test
 */
function activate_plugin(elt)
{
    	try
    		{
                waiting_box();
    	       var queryString =  {
    	                op:'activate_plugin',
    	                gDossier:elt.getAttribute('gDossier'),
    	                mecode:elt.getAttribute('me_code'),
    	                prid:elt.getAttribute('pr_id'),
    	                dep:elt.getAttribute('dep'),
    	                ord:elt.getAttribute('order'),
                        activate:elt.checked
    	            };
    	        var action = new Ajax.Request(
    					  "ajax_misc.php" ,
    					  {
    					      method:'GET',
    					      parameters:queryString,
    					      onFailure:ajax_misc_failure,
    					      onSuccess:function(req){
    							remove_waiting_box();
    	                        if (req.responseText == 'NOCONX') {
    	                            reconnect();
    	                            return;
    	                        }

                                  if (req.responseText != 'OK') {
                                      smoke.alert(req.responseText)
                                      elt.checked=false;
                                  }
    					      }
    					  }
    	              );
    		}catch( e)
    		{
    			alert_box(e.message);
    		}
}
/**********************************************************************************************/
/**
 * @class Widget
 *
*************************************************************************************************************/

Widget = function(dossier_id) {
    this.dossier_id=dossier_id;
}
/**
 * Display the widget in the elt box
 * @param box DOMID of the target
 * @param dossier_id
 * @param user_widget_id int
 * @param widget_code string
 * @param var_name string name of the variable
 */
Widget.prototype.display = function (box,user_widget_id,widget_code,var_name) {
    try {

        var queryString = {
            gDossier: this.dossier_id,
            'op': 'widget',
            'user_widget_id': user_widget_id,
            'widget_code': widget_code,
            'var_name':var_name,
            'action': 'widget.display'
        }
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'GET',
                parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req) {
                    if (req.responseText == 'NOCONX') {
                        reconnect();
                        return;
                    }
                    id$(box).replace(req.responseText);

                }
            }
        );
    } catch (e) {
        alert_box(e.message);
    }

}

/**
 * Manage the widget
 * @param dossier_id
 * @returns {boolean}
 */
Widget.prototype.manage = function () {
    try {
        this.show_ident();
        var box = 'widget_box_id';
        var queryString = {
            gDossier: this.dossier_id,
            'op': 'widget',
            'action': 'widget.manage'
        }
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'GET',
                parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req) {
                    if (req.responseText == 'NOCONX') {
                        reconnect();
                        return;
                    }
                    var style = 'position:absolute;';
                    var y = calcy(200);
                    style = style + ' ;top : ' + y + 'px';

                    add_div({id: box, cssclass: 'inner_box', html: loading(), style: style,drag:false})

                    id$(box).update(req.responseText);
                }
            }
        );
    } catch (e) {
        alert_box(e.message);
        console.error("widget_manage" + e.message);
    }
    return false;
}
/**
 * create a list  of sortable elements
 */
Widget.prototype.create_sortable=function() {

    Sortable.create('contain_widget',{tag:'li',onUpdate:function(){ id$('order_widget_hidden').value=Sortable.serialize('contain_widget')}})
    id$('order_widget_hidden').value=Sortable.serialize('contain_widget');
}
/**
 * Save the order of widget
 **/
Widget.prototype.save = function () {
    	try
    		{
                var here = this;
    	        var dgbox="widget_box_id";
    	        waiting_box();

    	        // For form , most of the parameters are in the FORM
    	        // method is then POST
    	         //var queryString=id$(p_form_id).serialize(true);

    	       var queryString = {
    	                op : 'widget',
    	                action : 'widget.save',
    	                gDossier: this.dossier_id,
                        param : Sortable.serialize('contain_widget')
    	            };
    	        var action = new Ajax.Request(
    					  "ajax_misc.php" ,
    					  {
    					      method:'GET',
    					      parameters:queryString,
    					      onFailure:ajax_misc_failure,
    					      onSuccess:function(req){
    							remove_waiting_box();
    	                        if (req.responseText == 'NOCONX') {
    	                            reconnect();
    	                            return;
    	                        }
    							removeDiv(dgbox)
                                here.refresh();

    					      }
    					  }
    	              );
    		}catch( e)
    		{
    			alert_box(e.message);
    		}
    this.remove_ident();
}
/**
 * refresh the DASHBOARD (dashboard_div_id)
 */
Widget.prototype.refresh = function () {
    try {
        var here = this;
        var dgbox='dashboard_div_id'
        var queryString = {
            op : 'widget',
            action : 'widget.refresh',
            gDossier: this.dossier_id
        };
        var action = new Ajax.Request(
                  "ajax_misc.php" ,
                  {
                      method:'GET',
                      parameters:queryString,
                      onFailure:ajax_misc_failure,
                      onSuccess:function(req){
                        if (req.responseText == 'NOCONX') {
                            reconnect();
                            return;
                        }

                        id$(dgbox).replace(req.responseText);

                      }
                  }
              );
        }catch( e) {
        console.error("widget.refresh "+e.message)
        }
}
/**
 * delete a widget : remove from the list
 * @param user_widget_id {integer}
 */
Widget.prototype.delete=function (user_widget_id) {
    id$('elt_'+user_widget_id).remove()
    id$('order_widget_hidden').value=Sortable.serialize('contain_widget');
}
/**
 * display list widget we can add
 */
Widget.prototype.input = function () {
    try {
        var box="widget_box_select_id";

        var queryString = {
            op: 'widget',
            action: 'widget.input',
            gDossier: this.dossier_id
        };
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'GET',
                parameters: queryString,
                onFailure: ajax_misc_failure,
                onSuccess: function (req) {
                    remove_waiting_box();
                    if (req.responseText == 'NOCONX') {
                        reconnect();
                        return;
                    }
                    var style = 'position:absolute;';
                    var y = calcy(200);
                    style = style + ' ;top : ' + y + 'px';

                    add_div({id: box, cssclass: 'inner_box', html: loading(), style: style})

                    id$(box).update(req.responseText);


                }
            }
        );
    } catch (e) {
        alert_box(e.message);
    }
}
/**
 * add a widget for  the user , refresh the dashboard afterward
 * @param widget_code {string}
 */
Widget.prototype.add=function (widget_code) {
    	try
    		{
                here=this;
                var param = {};
                if (document.getElementById(widget_code+"_param")) {
                    param=id$(widget_code+"_param").serialize()
                }
                query = {
                    op : 'widget',
                    action : 'widget.insert',
                    gDossier: this.dossier_id,
                    param : param,
                    widget_code:widget_code
                }
    	        var action = new Ajax.Request(
    					  "ajax_misc.php" ,
    					  {
    					      method:'GET',
    					      parameters:query,
    					      onFailure:ajax_misc_failure,
    					      onSuccess:function(req){
    	                        if (req.responseText == 'NOCONX') {
    	                            reconnect();
    	                            return;
    	                        }
                                var new_element=new Element("li");
                                id$('contain_widget').appendChild(new_element);
                                new_element.replace(req.responseText)
                                removeDiv('widget_box_select_id')
                                here.create_sortable()

    					      }
    					  }
    	              );
    		}catch( e)
    		{
    			alert_box(e.message);
    		}

}
/**
 * Show the number in the widget to improve the ergonomy
 */
Widget.prototype.show_ident = function ()
{
    var aBox = document.getElementsByClassName('widget-box')  ;
    var nb=aBox .length
    var idx=1;
    for (var e=0;e <nb; e++) {
        if (aBox[e].visible)
        {
            var spanx=new Element('span');
            spanx.addClassName("box_ident");
            aBox[e].insertBefore(spanx,aBox[e].firstChild);
            spanx.update(idx);
            idx++
        }
    }
}
/**
 * Hide the number of the widget
 */
Widget.prototype.remove_ident = function ()
{
    while (true) {
        var elt=document.getElementsByClassName("box_ident");
        if ( elt.length == 0) break;
        elt[0].remove()
    }
}

/**
 * Put the widget in full size
 * @param widget_domid {string} dom id of the widget to toggle the size
 */
Widget.prototype.toggle_full_size=function (widget_domid) {
    if ( id$(widget_domid).hasClassName('widget-full_size')) {
        id$(widget_domid).removeClassName('widget-full_size');
    } else {
        id$(widget_domid).addClassName('widget-full_size');

        layer++;
        id$(widget_domid).style.zIndex=layer;
    }

};


/**
 * EXPERIMENTAL
(function(){window.addEventListener("beforeunload", (event) => {waiting_box()});})();

(function(){window.addEventListener("onload", (event) => {remove_waiting_box()});})();
*/

var bookmark=new Bookmark();
