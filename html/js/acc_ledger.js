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
 * @file
 * javascript script for the ledger in accountancy,
 * compute the sum, add a row at the table..
 *
 */
var layer = 1;

/**
 * \fn
 * \brief update the list of available predefined operation when we change the ledger.
 */
function update_predef(p_type, p_direct, p_ac) {
    var jrn = id$("p_jrn").value;
    var dossier = id$("gDossier").value;
    var querystring = 'gDossier=' + dossier + '&l=' + jrn + '&t=' + p_type + '&d=' + p_direct + "&op=up_predef&ac=" + p_ac;
    id$("p_jrn_predef").value = jrn;
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: querystring,
            onFailure: error_get_predef,
            onSuccess: function (req) {
                try {
                    id$('info_div').innerHTML = "ok";
                    var answer = req.responseXML;
                    var a = answer.getElementsByTagName('code');
                    var html = answer.getElementsByTagName('value');
                    if (a.length == 0) {
                        var rec = req.responseText;
                        alert_box(content[48] + rec);
                    }
                    var code_html = getNodeText(html[0]);
                    code_html = unescape_xml(code_html);
                    // document.getElementsByName(name_ctl)[0].value = code_html;
                    id$('modele_op_div').innerHTML = code_html;
                } catch (e) {
                    id$('info_div').innerHTML = e.message;
                }
            }
        }
    );
}

/**
 *  update the list of payment method when we change the ledger.
 */
function update_pay_method() {
    waiting_box();
    var jrn = id$("p_jrn").value;
    var dossier = id$("gDossier").value;
    var querystring = 'gDossier=' + dossier + '&l=' + jrn + "&op=up_pay_method";
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: querystring,
            onFailure: error_get_predef,
            onSuccess: function (req) {
                remove_waiting_box();
                var answer = req.responseText;
                id$('payment').innerHTML = answer;
            }
        }
    );
}

/**
 *  update the list of additional tax
 */
function update_other_tax() {
    waiting_box();
    var jrn = id$("p_jrn").value;
    var dossier = id$("gDossier").value;
    var querystring = {gDossier: dossier, jrn_id: jrn, op: "up_other_tax"};
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: querystring,
            onFailure: error_get_predef,
            onSuccess: function (req) {
                remove_waiting_box();
                var answer = req.responseText;
                answer.evalScripts();
                id$('additional_tax_div').innerHTML = answer;
            }
        }
    );
}

/**
 * update ctl id =jrn_name with the value of p_jrn
 */
function update_name() {
    var jrn_id = id$('p_jrn').value;
    var dossier = id$("gDossier").value;
    var querystring = 'gDossier=' + dossier + '&l=' + jrn_id + "&op=ledger_description";
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: querystring,
            onFailure: error_get_pj,
            onSuccess: function (req) {
                id$('jrn_name_div').innerHTML = req.responseText;
            }
        }
    );

}

/**
 *  update the field predef
 */
function error_get_predef(request, json) {
    alert_box(content[49]);

}

/**
 *  update the list of available predefined operation when we change the ledger.
 */
function update_receipt() {
    var jrn = id$("p_jrn").value;
    var dossier = id$("gDossier").value;
    var querystring = 'gDossier=' + dossier + '&l=' + jrn + "&op=upd_receipt";
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: querystring,
            onFailure: error_get_pj,
            onSuccess: success_get_pj
        }
    );
}

/**
 * ask the name, quick_code of the bank for the ledger
 */
function update_bank() {
    var jrn = id$('p_jrn').value;
    var dossier = id$('gDossier').value;
    var qs = 'gDossier=' + dossier + '&op=bkname&p_jrn=' + jrn;
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: qs,
            onFailure: error_get_pj,
            onSuccess: success_update_bank
        }
    );

}

/**
 *  Update the number of rows when changing of ledger
 */
function update_row(ctl) {
    try {
        var row_to_keep = 3; /* Number of row to keep (head and foot)*/
        if (ctl === 'quick_item') {
            row_to_keep = 1;
        } /* for ODS , only 1 rows to keep */
        var jrn = id$('p_jrn').value;
        var dossier = id$('gDossier').value;
        var qs = encodeURI('gDossier=' + dossier + '&op=minrow&j=' + jrn + '&ctl=' + ctl);
        var current_row = parseFloat(id$('nb_item').value);
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get',
                parameters: qs,
                onFailure: null,
                onSuccess: function (request, json) {
                    try {
                        var answer = request.responseText.evalJSON(true);
                        var row = parseFloat(answer.row);

                        var table_to_update = id$(ctl);
                        if (current_row > row) {
                            // Too many row, we always must keep 2 rows for the sum
                            var delta = id$('nb_item').value - row;
                            var idx = id$('nb_item').value;
                            for (var i = 0; i < delta; i++) {
                                var pos_row = table_to_update.rows.length;
                                var cell0 = table_to_update.rows[pos_row - row_to_keep].cells[0];
                                var cell0Element = cell0.childNodes;
                                var canDelete = true;
                                /**
                                 * prevent truncating
                                 */
                                cell0Element.forEach(function (x) {
                                        if (canDelete && x.nodeName == 'INPUT' && x.type === "text" && x.value) {
                                            canDelete = false;
                                        }
                                    }
                                );
                                /*
                                 * prevent truncating
                                 * For ODS we also need to check the second column
                                 * */
                                if (canDelete && ctl === 'quick_item') {
                                    var cell1 = table_to_update.rows[pos_row - row_to_keep].cells[1];
                                    var cell1Element = cell1.childNodes;
                                    cell1Element.forEach(function (x) {
                                        if (canDelete && x.nodeName == 'INPUT' && x.type === "text" && x.value) {
                                            canDelete = false;
                                        }
                                    });
                                }
                                if (canDelete) {
                                    table_to_update.deleteRow(pos_row - row_to_keep);
                                    idx--;
                                }

                            }
                            id$('nb_item').value = table_to_update.rows.length - row_to_keep;
                        }
                        if (current_row < row) {
                            // We need to add rows
                            var delta = row - current_row;
                            for (var i = 0; i < delta; i++) {
                                if (ctl == 'fin_item') {
                                    ledger_fin_add_row();
                                }
                                if (ctl == 'sold_item') {
                                    ledger_add_row();
                                }
                                if (ctl == 'quick_item') {
                                    quick_writing_add_row();
                                }
                            }
                        }
                    } catch (e) {
                        alert_box("update_row:01" + e.message);
                    }
                }
            }
        );
    } catch (e) {
        alert_box(e.message);
    }
}

/**
 * @brief hide or show the column quantity
 */
function update_visibility_quantity() {
    var jrn = id$("p_jrn").value;
    var dossier = id$("gDossier").value;
    var querystring = 'gDossier=' + dossier + '&l=' + jrn + "&op=update_visibility_quantity";
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: querystring,
            onSuccess: function (req) {
                try {
                    // retrieve quantity
                    var quantity_col = document.getElementsByClassName("col_quant");
                    for (var i = 0; i < quantity_col.length; i++) {
                        if (req.responseText == "0") {
                            // hide the columns quantity and return
                            quantity_col[i].hide();
                            quantity_col[i].addClassName('d-none');
                        }
                        if (req.responseText == "1") {
                            // show the columns quantity and return
                            quantity_col[i].show();
                            quantity_col[i].removeClassName('d-none');
                        }
                    }
                } catch (e) {
                    console.error("update_visibility_quantity" + e.message);
                }


            }
        }
    );
}

/**
 *  Put into the span, the name of the bank, the bank account
 * and the quick_code
 */
function success_update_bank(req) {
    try {
        var answer = req.responseXML;
        var a = answer.getElementsByTagName('code');
        var html = answer.getElementsByTagName('value');
        if (a.length == 0) {
            var rec = req.responseText;
            alert_box('UPDBK-' + content[48] + rec);
        }
        var name_ctl = a[0].firstChild.nodeValue;
        var code_html = getNodeText(html[0]);
        code_html = unescape_xml(code_html);
        id$(name_ctl).innerHTML = code_html;
    } catch (e) {
        alert_box("success_update_bank" + e.message);
    }
}

/**
 *  call ajax, ask what is the last date for the current ledger
 */
function get_last_date() {
    var jrn = id$('p_jrn').value;
    var dossier = id$('gDossier').value;
    var qs = 'gDossier=' + dossier + '&op=lastdate&p_jrn=' + jrn;
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: qs,
            onFailure: error_get_pj,
            onSuccess: success_get_last_date
        }
    );
}

/**
 *  callback ajax, set the ctl with the last date from the ledger
 */
function success_get_last_date(req) {
    try {
        var answer = req.responseXML;
        var a = answer.getElementsByTagName('code');
        var html = answer.getElementsByTagName('value');
        if (a.length == 0) {
            var rec = req.responseText;
            alert_box('GETLASTDA:' + content[48] + rec);
        }
        var name_ctl = a[0].firstChild.nodeValue;
        var code_html = getNodeText(html[0]);
        code_html = unescape_xml(code_html);
        document.getElementsByName(name_ctl)[0].value = code_html;
    } catch (e) {
        alert_box(e.message);
    }
}

/**
 *  update the field predef
 */
function success_get_pj(request, json) {

    var answer = request.responseText.evalJSON(true);
    var obj = id$("e_pj");
    obj.value = '';
    if (answer.length == 0)
        return;
    obj.value = answer.pj;
    id$("e_pj_suggest").value = answer.pj;
}

/**
 *  update the field predef
 */
function error_get_pj(request, json) {
    alert_box("GETPJ:" + content[48]);
}

/**
 *  add a line in the form for the ledger fin
 */
function ledger_fin_add_row() {
    var style = 'class="input_text"';
    var mytable = id$("fin_item").tBodies[0];
    var line = mytable.rows.length;
    var row = mytable.insertRow(line);
    var nb = id$("nb_item");
    var rowToCopy = mytable.rows[1];
    var nNumberCell = rowToCopy.cells.length;
    for (var e = 0; e < nNumberCell; e++) {
        var newCell = row.insertCell(e);
        if (e == 0) {
            newCell.id = 'tdchdate' + nb.value;
        }
        var tt = rowToCopy.cells[e].innerHTML;
        var new_tt = tt.replace(/e_other0/g, "e_other" + nb.value);
        new_tt = new_tt.replace(/e_other0_comment/g, "e_other" + nb.value + '_comment');
        new_tt = new_tt.replace(/e_other_name0/g, "e_other_name" + nb.value);
        new_tt = new_tt.replace(/e_other0_amount/g, "e_other" + nb.value + '_amount');
        new_tt = new_tt.replace(/e_concerned0/g, "e_concerned" + nb.value);
        new_tt = new_tt.replace(/e_other0_label/g, "e_other" + nb.value + '_label');
        new_tt = new_tt.replace(/dateop0/g, "dateop" + nb.value);
        newCell.innerHTML = new_tt;
        newCell.className = rowToCopy.cells[e].className;
        new_tt.evalScripts();
    }
    id$("e_other" + nb.value).value = "";
    id$("e_other_name" + nb.value).value = "";
    id$("e_other" + nb.value + '_amount').value = "0";
    id$("e_other" + nb.value + '_comment').value = "";
    id$("e_concerned" + nb.value).value = "";

    var ch = id$('chdate').options[id$('chdate').selectedIndex].value;
    if (ch == 1) {
        id$('tdchdate' + nb.value).hide();
    }
    nb.value++;
}

/**
 * Add multiple row
 * @param string p_elid is the id of element  with the number of rows to add, and p_elid+"_ledger" is the type of ledger : M : Misc Operation O : Sales or purchase and F for financial
 */
function ledger_add_multiple(p_elid) {
    var nbrow = id$(p_elid).value;
    if (nbrow == NaN) {
        nbrow = 1;
    }
    var type_ledger = id$(p_elid + "_ledger").value;
    var i = 0;
    for (i = 0; i < nbrow; i++) {
        if (type_ledger == 'O') {
            ledger_add_row();
        }
        if (type_ledger == 'F') {
            ledger_fin_add_row();
        }
        if (type_ledger == 'M') {
            quick_writing_add_row();
        }
    }
    if (type_ledger == 'M') {
        var aCheckBox = $$('.debit-credit')
        aCheckBox.forEach((item) => display_dcside(item))
    }
}

/**
 *  add a line in the form for the purchase ledger
 * @param p_dossier folder id
 * @param p_table_name
 */
function ledger_add_row() {
    try {
        style = 'class="input_text"';
        var mytable = id$("sold_item").tBodies[0];
        var ofirstRow = mytable.rows[1];
        var line = mytable.rows.length;
        var nCell = mytable.rows[1].cells.length;
        var row = mytable.insertRow(line);
        var nb = id$("nb_item");
        for (var e = 0; e < nCell; e++) {
            var newCell = row.insertCell(e);
            var tt = ofirstRow.cells[e].innerHTML;
            var new_tt = tt.replace(/march0/g, "march" + nb.value);
            new_tt = new_tt.replace(/quant0/g, "quant" + nb.value);
            new_tt = new_tt.replace(/sold\(0\)/g, "sold(" + nb.value + ")");
            new_tt = new_tt.replace(/compute_ledger\(0\)/g, "compute_ledger(" + nb.value + ")");
            new_tt = new_tt.replace(/clean_tva\(0\)/g, "clean_tva(" + nb.value + ")");
            newCell.innerHTML = new_tt;
            newCell.className = ofirstRow.cells[e].className;
            new_tt.evalScripts();
        }

        id$("e_march" + nb.value + "_label").innerHTML = '';
        id$("e_march" + nb.value + "_label").value = '';
        id$("e_march" + nb.value + "_price").value = '0';
        id$("e_march" + nb.value).value = "";
        id$("e_quant" + nb.value).value = "1";
        if (document.getElementById("e_march" + nb.value + "_tva_amount"))
            id$("e_march" + nb.value + "_tva_amount").value = 0;

        nb.value++;

        new_tt.evalScripts();
    } catch (e) {
        alert_box(e.message);
    }
}

/**
 *  compute the sum of a purchase, update the span tvac, htva and tva
 * all the needed data are taken from the document (hidden field :  gdossier)
 * @param the number of the changed ctrl
 */
function compute_ledger(p_ctl_nb) {
    var dossier = id$("gDossier").value;
    var a = -1;
    if (document.getElementById("e_march" + p_ctl_nb + '_tva_amount')) {
        a = trim(id$("e_march" + p_ctl_nb + '_tva_amount').value);
        id$("e_march" + p_ctl_nb + '_tva_amount').value = a;
    }
    if (!document.getElementById("e_march" + p_ctl_nb)) {
        return;
    }
    id$("e_march" + p_ctl_nb).value = trim(id$("e_march" + p_ctl_nb).value);
    var qcode = id$("e_march" + p_ctl_nb).value;

    if (qcode.length == 0) {
        clean_ledger(p_ctl_nb);
        refresh_ledger();
        return;
    }
    /*
     * if tva_id is empty send a value of -1
     */
    var tva_id = -1;
    if (document.getElementById('e_march' + p_ctl_nb + '_tva_id')) {
        tva_id = id$('e_march' + p_ctl_nb + '_tva_id').value;
        if (trim(tva_id) == '') {
            tva_id = -1;
        }
    }

    id$('e_march' + p_ctl_nb + '_price').value = trim(id$('e_march' + p_ctl_nb + '_price').value);
    var price = id$('e_march' + p_ctl_nb + '_price').value;

    id$('e_quant' + p_ctl_nb).value = trim(id$('e_quant' + p_ctl_nb).value);
    var quantity = id$('e_quant' + p_ctl_nb).value;
    let other_tax = document.getElementById("other_tax");
    let other_tax_id = (other_tax && other_tax.checked) ? other_tax.value : -1;

    var querystring = {
        gDossier: dossier,
        c: qcode,
        t: tva_id,
        p: price,
        q: quantity,
        n: p_ctl_nb,
        'other_tax_id': other_tax_id
    };
    var action = new Ajax.Request(
        "compute.php",
        {
            method: 'get',
            parameters: querystring,
            onFailure: error_compute_ledger,
            onSuccess: success_compute_ledger
        }
    );
}

/**
 * refresh the purchase screen, recompute vat, total...
 */
function refresh_ledger() {
    var tva = 0;
    var htva = 0;
    var tvac = 0;

    nb_item = id$("nb_item").value;
    for (var i = 0; i < nb_item; i++) {
        if (document.getElementById('tva_march' + i))
            tva += id$('tva_march' + i).value * 1;
        if (document.getElementById('htva_march' + i))
            htva += id$('htva_march' + i).value * 1;
        if (document.getElementById('tvac_march' + i))
            tvac += id$('tvac_march' + i).value * 1;
    }
    id_tva = id$("tva");
    id_htva = id$("htva");
    id_tvac = id$("tvac");
    id_other_tax = document.getElementById("other_tax_amount");
    if (id_tva)
        id_tva.innerHTML = Math.round(tva * 100) / 100;
    if (id_htva)
        id_htva.innerHTML = Math.round(htva * 100) / 100;
    if (id_other_tax) {
        let total_operation = tvac + parseFloat(id_other_tax.value);
        id$('total_operation_other_tax').innerHTML = Math.round(total_operation * 100) / 100;
    }
    if (id_tvac)
        id_tvac.innerHTML = Math.round(tvac * 100) / 100;


}

/**
 * update the field htva, tva_id and tvac, callback function for  compute_sold
 * it the field TVA in the answer contains NA it means that VAT is appliable and then do not
 * update the VAT field except htva_martc
 */
function success_compute_ledger(request, json) {
    var answer = request.responseText.evalJSON(true);
    var ctl = answer.ctl;
    var rtva = answer.tva;
    var rhtva = answer.htva;
    var rtvac = answer.tvac;
    let other_tax = id$("other_tax_amount")
    if (other_tax) {
        other_tax.value = answer.other_tax;
    }
    if (rtva == 'NA') {
        var rhtva = answer.htva * 1;
        id$('htva_march' + ctl).value = rhtva;
        id$('tvac_march' + ctl).value = rtvac;
        id$('sum').show();
        refresh_ledger();
        CurrencyCompute('p_currency_rate', 'p_currency_euro');

        return;
    }
    rtva = answer.tva * 1;


    id$('sum').show();
    if (document.getElementById('e_march' + ctl + '_tva_amount').value == "" ||
        document.getElementById('e_march' + ctl + '_tva_amount').value == 0) {
        id$('tva_march' + ctl).value = rtva;
        id$('e_march' + ctl + '_tva_amount').value = rtva;
    } else {
        id$('tva_march' + ctl).value = id$('e_march' + ctl + '_tva_amount').value;
    }
    id$('htva_march' + ctl).value = Math.round(parseFloat(rhtva) * 100) / 100;
    var tmp1 = Math.round(parseFloat(id$('htva_march' + ctl).value) * 100) / 100;
    var tmp2 = Math.round(parseFloat(id$('tva_march' + ctl).value) * 100) / 100;
    id$('tvac_march' + ctl).value = Math.round((tmp1 + tmp2) * 100) / 100;
    refresh_ledger();
    CurrencyCompute('p_currency_rate', 'p_currency_euro');

}

/**
 *  callback error function for  compute_sold
 */
function error_compute_ledger(request, json) {
    alert_box('Ajax does not work');
}

function compute_all_ledger() {
    var loop = 0;
    let nb_item = id$("nb_item").value;
    for (loop = 0; loop < nb_item; loop++) {
        compute_ledger(loop);
    }
    var tva = 0;
    var htva = 0;
    var tvac = 0;

    for (var i = 0; i < nb_item; i++) {
        if (document.getElementById('tva_march'))
            tva += id$('tva_march' + i).value * 1;
        if (document.getElementById('htva_march' + i))
            htva += id$('htva_march' + i).value * 1;
        if (document.getElementById('tvac_march' + i))
            tvac += id$('tvac_march' + i).value * 1;
    }
    id_other_tax = document.getElementById("other_tax_amount");
    if (document.getElementById('tva'))        id$('tva').innerHTML = Math.round(tva * 100) / 100;
    if (document.getElementById ('htva'))
        id$('htva').innerHTML = Math.round(htva * 100) / 100;
    if (id_other_tax) {
        tvac += id_other_tax.value;
    }
    if (document.getElementById('tvac'))
        id$('tvac').innerHTML = Math.round(tvac * 100) / 100;


}

function clean_tva(p_ctl) {
    if (document.getElementById('e_march' + p_ctl + '_tva_amount'))
        id$('e_march' + p_ctl + '_tva_amount').value = 0;
}

function clean_ledger(p_ctl_nb) {
    if (document.getElementById("e_march" + p_ctl_nb)) {
        id$("e_march" + p_ctl_nb).value = trim(id$("e_march" + p_ctl_nb).value);
    }
    if (document.getElementById('e_march' + p_ctl_nb + '_price')) {
        id$('e_march' + p_ctl_nb + '_price').value = '';
    }
    if (document.getElementById('e_quant' + p_ctl_nb)) {
        id$('e_quant' + p_ctl_nb).value = '1';
    }
    if (document.getElementById('tva_march' + p_ctl_nb + '_show')) {
        id$('tva_march' + p_ctl_nb + '_show').value = '0';
    }
    if (document.getElementById('tva_march' + p_ctl_nb)) {
        id$('tva_march' + p_ctl_nb).value = 0;
    }
    if (document.getElementById('htva_march' + p_ctl_nb)) {
        id$('htva_march' + p_ctl_nb).value = 0;
    }
    if (document.getElementById('tvac_march' + p_ctl_nb)) {
        id$('tvac_march' + p_ctl_nb).value = 0;
    }

}

/**
 *  add a line in the form for the quick_writing
 */
function quick_writing_add_row() {
    style = 'class="input_text"';
    var mytable = id$("quick_item").tBodies[0];
    var nNumberRow = mytable.rows.length;
    var oRow = mytable.insertRow(nNumberRow);
    var rowToCopy = mytable.rows[1];
    var nNumberCell = rowToCopy.cells.length;
    var nb = id$("nb_item");

    var oNewRow = mytable.insertRow(nNumberRow);
    for (var e = 0; e < nNumberCell; e++) {
        var newCell = oRow.insertCell(e);
        var tt = rowToCopy.cells[e].innerHTML;
        new_tt = tt.replace(/qc_0/g, "qc_" + nb.value);
        new_tt = new_tt.replace(/amount0/g, "amount" + nb.value);
        new_tt = new_tt.replace(/poste0/g, "poste" + nb.value);
        new_tt = new_tt.replace(/ck0/g, "ck" + nb.value);
        new_tt = new_tt.replace(/ld0/g, "ld" + nb.value);
        newCell.innerHTML = new_tt;
        newCell.className = rowToCopy.cells[e].className;
        new_tt.evalScripts();

    }
    var ck = id$('ck' + nb.value);
    ck.addEventListener('click', function (event) {
        display_range_dcside(event, ck);
        display_dcside(ck);
    });


    id$("qc_" + nb.value).value = "";
    id$("amount" + nb.value).value = "";
    id$("poste" + nb.value).value = "";
    id$("ld" + nb.value).value = "";


    nb.value++;

}

function RefreshMe() {
    window.location.reload();
}


function go_next_concerned() {
    var form = document.forms[1];

    for (var e = 0; e < form.elements.length; e++) {
        var elmt = form.elements[e];
        if (elmt.type == "checkbox") {
            if (elmt.checked == true) {
                return confirm(content[52]);
            }
        }
    }
    return true;
}

/**
 *  View the history of an account
 * @param {type} p_value
 * @param {type} dossier
 * @returns {undefined}
 */
function view_history_account(p_value, dossier, p_exercice) {
    var layer=get_next_layer();
    var idbox = 'det' + layer;
    var popup = {'id': idbox, 'cssclass': 'inner_box', 'html': loading(), 'drag': false};

    var querystring = {
        'gDossier': dossier,
        'act': 'de',
        'pcm_val': p_value,
        'div': idbox,
        'l': layer,
        'op': 'history',
        'exercice': p_exercice
    };
    waiting_box();

    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: querystring,
            onFailure: error_box,
            onSuccess: function (req, xml) {
                remove_waiting_box();
                if (req.responseText === 'NOCONX') { reconnect();return;}

                add_div(popup);
                success_box(req, xml);
                id$(idbox).style.top = calcy(140 + (layer * 3)) + "px";
                $id$(idbox).setStyle({top:calcy(140 + (layer * 3)) + "px"
                ,"z-index":layer})
            }
        }
    );

}

/**
 *  View the history of an account
 * @param {type} p_value
 * @param {type} dossier
 * @returns {undefined}
 */
function view_history_anc_account(p_value, dossier, p_exercice) {
    var layer=get_next_layer();
    var idbox = 'det' + layer;
    var popup = {'id': idbox, 'cssclass': 'inner_box', 'html': loading(), 'drag': false};

    var querystring = {
        'gDossier': dossier,
        'op': 'history_anc_account',
        'po_id': p_value,
        'div': idbox,
        'l': layer,
        'act': 'history',
        'exercice': p_exercice
    };
    waiting_box();

    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: querystring,
            onFailure: error_box,
            onSuccess: function (req, xml) {
                remove_waiting_box();
                if (req.responseText === 'NOCONX') { reconnect();return;}

                add_div(popup);
                  id$(idbox).setStyle(
                            {
                                top:calcy(140 + (layer * 3)) + "px"
                                ,"z-index":get_next_layer()
                            }
                    )
            }
        }
    );

}

/**
 *  Change the view of account history
 * @param {type} obj
 * @returns {Boolean}
 */
function update_history_account(obj) {
    try {
        var querystring = {
            "l": obj.div,
            "div": obj.div,
            "gDossier": obj.gDossier,
            "pcm_val": obj.pcm_val,
            "ex": obj.select.options[obj.select.selectedIndex].text,
            "op": "history",
            "exercice": obj.exercice
        };
        waiting_box();
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get',
                parameters: querystring,
                onFailure: error_box,
                onSuccess: function (req, xml) {
                    remove_waiting_box();
                    if (req.responseText === 'NOCONX') { reconnect();return;}

                    success_box(req, xml);
                    id$(obj.div).setStyle(
                            {
                                top:calcy(140 + (layer * 3)) + "px"
                                ,"z-index":get_next_layer()
                            }
                    )
                }
            });
    } catch (e) {
        alert_box("update_history_account error " + e.message);
    }

    return false;
}

/*!\brief Change the view of card history
 * \param p_value f_id of the card
 */
function view_history_card(p_value, dossier, p_exercice) {
    var layer=get_next_layer();
    var idbox = 'det' + layer;
    var popup = {
        'id': idbox,
        'cssclass': 'inner_box',
        'html': loading(),
        'drag': false
    };
    var querystring = {
        'gDossier': dossier,
        'act': 'de',
        'f_id': p_value,
        'div': idbox,
        "l": layer,
        "op": "history",
        "exercice": p_exercice
    };
    waiting_box();
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: querystring,
            onFailure: error_box,
            onSuccess: function (req, xml) {
                remove_waiting_box();
                if (req.responseText === 'NOCONX') { reconnect();return;}

                add_div(popup);
                success_box(req, xml);
                id$(idbox).setStyle(
                            {
                                top:calcy(140 + (layer * 3)) + "px"
                                ,"z-index":get_next_layer()
                            }
                    )
            }
        }
    );
}
/*!
 * \brief list followup of a card
 * \param p_value int fiche.f_id of the card
 */
function view_followup_card(p_value, dossier) {
    var layer=get_next_layer();
    var idbox = 'detfu' + layer;
    var popup = {
        'id': idbox,
        'cssclass': 'inner_box',
        'html': loading(),
        'drag': false
    };
    var querystring = {
        'gDossier': dossier,
        'f_id': p_value,
        'div': idbox,
        "l": layer,
        "op": "view_followup_card",
    };
    waiting_box();
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: querystring,
            onFailure: error_box,
            onSuccess: function (req, xml) {
                remove_waiting_box();
                if (req.responseText === 'NOCONX') { reconnect();return;}

                add_div(popup);
                id$(idbox).update(req.responseText);
                  id$(idbox).setStyle(
                            {
                                top:calcy(140 + (layer * 3)) + "px"
                                ,"z-index":get_next_layer()
                            }
                    )
            }
        }
    );
}

/**
 *  update history view after changing the exercice
 * @param {type} obj
 * @returns {Boolean}
 */
function update_history_card(obj) {
    try {
        var querystring = {
            "l": obj.div,
            "div": obj.div,
            "gDossier": obj.gDossier,
            "f_id": obj.f_id,
            "ex": obj.select.options[obj.select.selectedIndex].text,
            "op": "history",
            "exercice": obj.exercice
        };
        waiting_box();
        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get',
                parameters: querystring,
                onFailure: error_box,
                onSuccess: function (req, xml) {
                    if (req.responseText === 'NOCONX') { reconnect();return;}

                    remove_waiting_box();
                    success_box(req, xml);
                      id$(obj.div).setStyle(
                            {
                                top:calcy(140 + (layer * 3)) + "px"
                                ,"z-index":get_next_layer()
                            }
                    )
                }
            });
    } catch (e) {
        alert_box("update_history_account error " + e.message);
    }

    return false;
}

/**
 * remove an Operation
 *@param p_jr_id is the jrn.jr_id
 *@param dossier
 *@param the div
 */
function removeOperation(p_jr_id, dossier, div) {
    waiting_box();
    var qs = {
        "gDossier": dossier,
        "op": "ledger",
        "act": "rmop",
        "div": div,
        "jr_id": p_jr_id
    };
    new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: qs,
            onFailure: error_box,
            onSuccess: infodiv
        }
    );

}

/**
 * reverse an Operation
 *@param pointer to the FORM
 */
function reverseOperation(obj) {
    var qs = id$(obj).serialize() + "&op=ledger";
    id$('ext' + obj.divname).style.display = 'none';
    waiting_box();
    new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: qs,
            onFailure: error_box,
            onSuccess: function (req) {
                try {
                    var action = new Ajax.Request(
                        "ajax_misc.php",
                        {
                            method: 'get',
                            parameters: {
                                "gDossier": obj["gDossier"].value,
                                "op": "ledger",
                                "act": "de",
                                "div": obj['div'].value,
                                "jr_id": obj['jr_id'].value
                            },
                            onFailure: error_box,
                            onSuccess: function (xml, txt) {

                                success_box(xml, txt);
                                infodiv(req);
                            }
                        });
                } catch (ex) {
                    smoke.alert(ex.message);
                }
            }
        }
    );

    return false;
}

/*!
 * \brief Show the details of an operation
 * \param p_value jrn.jr_id
 * \param dossier dossier id
 */
function modifyOperation(p_value, dossier) {
    var layer=get_next_layer();
    var id_div = 'det' + layer;
    waiting_box();
    var querystring = {
        "gDossier": dossier,
        "op": "ledger",
        "act": "de",
        "div": id_div,
        "jr_id": p_value
    };
    var action = new Ajax.Request(
        "ajax_misc.php",
        {
            method: 'get',
            parameters: querystring,
            onFailure: error_box,
            onSuccess: function (xml, txt) {
                if (xml.responseText === 'NOCONX') {
                    reconnect();
                    return;
                }
                var popup = {
                    'id': id_div, 'cssclass': 'inner_box'
                    , 'html': "", 'drag': false
                };
                remove_waiting_box();
                add_div(popup);
                success_box(xml, txt);
                id$(id_div).setStyle({
                    top:calcy(100 + (layer * 3)) + "px"
                    ,"z-index":layer
                    ,position:"absolute"
                });
                }              
        }
    );
}

/*!\brief
 * \param p_value jrn.jr_id
 */

function viewOperation(p_value, p_dossier) {
    modifyOperation(p_value, p_dossier)
}

function dropLink(p_dossier, p_div, p_jr_id, p_jr_id2) {
    var querystring = {
        "gDossier": p_dossier,
        "op": "ledger",
        "act": "rmr",
        "div": p_div,
        "jr_id": p_jr_id,
        "jr_id2": p_jr_id2
    };
    var action = new Ajax.Request('ajax_misc.php',
        {
            method: 'get',
            parameters: querystring,
            onFailure: null,
            onSuccess: null
        }
    );
}

/**
 * this function is called before the querystring is send to the
 * fid2.php, add a filter based on the ledger 'p_jrn'
 *@param obj is the input field
 *@param queryString is the queryString to modify
 *@see ICard::input
 */
function filter_card(obj, queryString) {
    jrn = id$('p_jrn').value;
    if (jrn == -1) {
        type = id$('ledger_type').value;
        queryString = queryString + '&type=' + type;
    } else {
        queryString = queryString + '&j=' + jrn;
    }
    return queryString;
}

/**
 * to display the lettering for the operation, call
 * ajax function
 *@param obj object attribut :  gDossier,j_id,obj_type
 */
function dsp_letter(obj) {
    try {
        //var queryString = 'gDossier=' + obj.gDossier + '&j_id=' + obj.j_id + '&op=dl' + '&ot=' + obj.obj_type+'&start='+obj.start;

        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get',
                parameters: obj,
                onFailure: error_dsp_letter,
                onSuccess: success_dsp_letter
            }
        );
        id$('search').style.display = 'none';
        id$('list').style.display = 'none';
        id$('detail').innerHTML = loading();
        id$('detail').style.display = 'block';
    } catch (e) {
        alert_box('dsp_letter failed  ' + e.message);
    }
}

function success_dsp_letter(req) {
    try {
        var answer = req.responseXML;
        var a = answer.getElementsByTagName('code');
        var html = answer.getElementsByTagName('value');
        if (a.length == 0) {
            var rec = req.responseText;
            alert_box('erreur :' + rec);
        }
        var name_ctl = a[0].firstChild.nodeValue;
        var code_html = getNodeText(html[0]);
        code_html = unescape_xml(code_html);
        id$('detail').innerHTML = code_html;
    } catch (e) {
        alert_box(e.message);
    }
    try {
        code_html.evalScripts();
    } catch (e) {
        alert_box("DSPLETTER1:" + content[48] + e.message);
    }

}

function error_dsp_letter(req) {
    alert_box("DSPLETTER2:" + content[48]);
}

function search_letter(obj) {
    try {
        var str_query = '';
        if (obj.elements['gDossier'])
            str_query = 'gDossier=' + obj.elements['gDossier'].value;
        if (obj.elements['j_id'])
            str_query += '&j_id=' + obj.elements['j_id'].value;
        if (obj.elements['obj_type'])
            str_query += '&obj_type=' + obj.elements['obj_type'].value;
        if (obj.elements['op'])
            str_query += '&op=' + obj.elements['op'].value;
        if (obj.elements['min_amount'])
            str_query += '&min_amount=' + obj.elements['min_amount'].value;
        if (obj.elements['max_amount'])
            str_query += '&max_amount=' + obj.elements['max_amount'].value;
        if (obj.elements['search_start'])
            str_query += '&search_start=' + obj.elements['search_start'].value;
        if (obj.elements['search_end'])
            str_query += '&search_end=' + obj.elements['search_end'].value;
        if (obj.elements['side'])
            str_query += '&side=' + obj.elements['side'].value;


        var action = new Ajax.Request(
            "ajax_misc.php",
            {
                method: 'get',
                parameters: str_query,
                onFailure: error_dsp_letter,
                onSuccess: success_dsp_letter
            }
        );
        id$('list').hide();
        id$('search').hide();
        id$('detail').innerHTML = loading();
        id$('detail').show();
    } catch (e) {
        alert_box('search_letter  ' + e.message);
    }
}

/**
 * save an operation in ajax, it concerns only the
 * comment, the pj and the rapt
 * the form elements are access by their name
 *@param obj form
 */
function op_save(obj) {
    try {
        var queryString = id$(obj).serialize(true);
        queryString ["gDossier"] = obj.gDossier.value;
        var rapt2 = "rapt" + obj.whatdiv.value;
        queryString ["rapt"] = id$(rapt2).value;
        queryString  ["jr_id"] = obj.jr_id.value;
        var jr_id = obj.jr_id.value;
        queryString ["div"] = obj.whatdiv.value;
        var divid = obj.whatdiv.value;
        queryString ["act"] = "save";
        queryString ["op"] = "ledger";
        queryString ["jr_note"]=encodeURI(tinyMCE.get("jrn_note"+divid).getContent());
        waiting_box();
        /*
         * Operation detail is in a new window
         */
        if (document.getElementById('inpopup')) {
            var action = new Ajax.Request('ajax_misc.php',
                {
                    method: 'post',
                    parameters: queryString,
                    onFailure: null,
                    onSuccess: function (req) {
                        remove_waiting_box();
                        var answer = req.getElementsByTagName('code');
                        if (answer[0] !== 'OK') {
                            console.error("D2. op_save")
                            smoke.alert(req.responseText);
                        }
                    }
                }
            );
            // window.close();
        } else {
            /*
             *Operation is in a modal box 
             */
            var action = new Ajax.Request('ajax_misc.php',
                {
                    method: 'post',
                    parameters: queryString,
                    onFailure: null,
                    onSuccess: function (req, json) {


                        if (req.responseXML == null) {
                            smoke.alert(req.responseText);
                        }
                        new Ajax.Request('ajax_misc.php', {
                            parameters: {
                                'gDossier': obj.gDossier.value,
                                'act': 'de',
                                'op': 'ledger',
                                'jr_id': jr_id,
                                'div': divid
                            },
                            onSuccess: function (xml) {
                                try {
                                    var answer = xml.responseXML;
                                    var html = answer.getElementsByTagName('code');
                                    id$(divid).innerHTML = unescape(getNodeText(html[0]));
                                    id$(divid).innerHTML.evalScripts();
                                    remove_waiting_box();
                                    noalyss.refresh_note(jr_id,obj.gDossier.value);
                                } catch (e) {
                                    console.error("D1. op_save")
                                    alert_box("1038" + e.message)
                                }
                            }
                        });

                    }
                });
        }
        return false;
    } catch (e) {
        console.error("F1. op_save")
        console.error(e.message)
        alert_box("op_save "+e.message);
        return false;
    }
}

function get_history_account(ctl, dossier) {
    if (document.getElementById(ctl).value != '') {
        view_history_account(id$(ctl).value, dossier);
    }
}

var previous = [];
var let_previous = "";

function show_reconcile(p_div, p_let) {
    try {
        if (previous.length != 0 || p_let == let_previous) {
            var count_elt = previous.length;
            var i = 0;
            for (i = 0; i < count_elt; i++) {
                previous[i].style.backgroundColor = '';
                previous[i].style.color = '';
                previous[i].style.fontWeight = "";
            }
        }
        var name = 'tr_' + p_let + '_' + p_div;
        var elt = document.getElementsByName(name);
        if (p_let != let_previous) {

            previous = elt;
            var count_elt = elt.length;
            var i = 0;
            for (i = 0; i < count_elt; i++) {
                elt[i].style.backgroundColor = '#000066';
                elt[i].style.color = 'white';
                elt[i].style.fontWeight = 'bolder';

            }
            let_previous = p_let;
        } else {
            let_previous = "";
        }

    } catch (e) {
        alert_box(e.message);
    }


}

/**
 *  add a line in the form for the purchase ledger
 */
function gestion_add_row() {
    try {
        style = 'class="input_text"';
        var mytable = id$("art").tBodies[0];
        var ofirstRow = mytable.rows[1];
        var line = mytable.rows.length;
        var nCell = mytable.rows[1].cells.length;
        var row = mytable.insertRow(line);
        var nb = id$("nb_item");
        for (var e = 0; e < nCell; e++) {
            var newCell = row.insertCell(e);
            var tt = ofirstRow.cells[e].innerHTML;
            var new_tt = tt.replace(/march0/g, "march" + nb.value);
            new_tt = new_tt.replace(/quant0/g, "quant" + nb.value);
            new_tt = new_tt.replace(/sold\(0\)/g, "sold(" + nb.value + ")");
            new_tt = new_tt.replace(/compute_ledger\(0\)/g, "compute_ledger(" + nb.value + ")");
            new_tt = new_tt.replace(/clean_tva\(0\)/g, "clean_tva(" + nb.value + ")");
            new_tt = new_tt + '<input type="hidden" id="tva_march' + nb.value + '">';
            new_tt = new_tt + '<input type="hidden" id="htva_march' + nb.value + '">';
            newCell.innerHTML = new_tt;
            if (mytable.rows[1].cells[e].hasClassName("num")) {
                newCell.addClassName("num");
            }
            new_tt.evalScripts();
        }

        id$("e_march" + nb.value + "_label").innerHTML = '&nbsp;';
        id$("e_march" + nb.value + "_label").value = '';
        id$("e_march" + nb.value + "_price").value = '0';
        id$("e_march" + nb.value).value = "";
        id$("e_quant" + nb.value).value = "1";
        id$('tvac_march' + nb.value).value = "0";
        if (document.getElementById("e_march" + nb.value + "_tva_amount"))
            id$("e_march" + nb.value + "_tva_amount").value = 0;

        nb.value++;

        new_tt.evalScripts();
    } catch (e) {
        alert_box(e.message);
    }

}

function document_remove(p_dossier, p_div, p_jrid) {
    smoke.confirm(content[50], function (e) {
        if (e) {
            new Ajax.Request('ajax_misc.php',
                {
                    parameters: {"op": "ledger", "gDossier": p_dossier, "div": p_div, "p_jrid": p_jrid, 'act': 'rmf'},
                    onSuccess: function (x) {
                        id$('receipt' + p_div).innerHTML = x.responseText;
                    }
                })
        }
    });
}

/***
 *  receive an object and display a list of filter + form to save one
 * fill up the span (id : {div}search_filter_span) with the name of the selected filter
 * Object = '{'div':'','type':'ALL','all_type':1,'dossier':'10104'}'
 * @see Acc_Ledger_Search
 */
function manage_search_filter(p_obj) {
    waiting_box();
    new Ajax.Request("ajax_misc.php", {
        method: 'get',
        parameters: {
            "op": "display_search_filter",
            "gDossier": p_obj.dossier,
            "div": p_obj.div,
            "ledger_type": p_obj.ledger_type,
            "all_type": p_obj.all_type
        },
        onSuccess: function (req) {
            remove_waiting_box();
            var x = posX;
            var y = calcy(200)
            create_div({
                'id': 'boxfilter' + p_obj.div,
                'cssclass': 'inner_box2',
                'html': req.responseText,
                'style': 'top:' + y + 'px;left:' + x + 'px;position:absolute;width:400px',
                drag: 1
            });
            id$('boxfilter' + p_obj.div).show();
        }
    });
}

/**
 * Send data from the form and record a new filter , the ajax answer is a json object
 * with the attribute filter_name,filter_id,status,message
 *
 * @param p_div prefix id of all concerned DOM Element
 * @param p_dossier
 * @see Acc_Ledger_Search
 */
function save_filter(p_div, p_dossier) {
    var elt = ['ledger_type', 'nb_jrn', 'date_start', 'date_end',
        'date_paid_start', 'date_paid_end', 'desc', 'amount_min', 'amount_max', 'qcode', 'accounting',
        'operation_filter', 'tag_option', 'p_currency_code', 'tva_id_search'];
    var eltValue = {};
    var i = 0;
    eltValue['gDossier'] = p_dossier;
    eltValue['op'] = "save_filter";
    eltValue['div'] = p_div;
    eltValue['filter_name'] = id$(p_div + "filter_new").value;
    // Get all elt from the form
    for (var i = 0; i < elt.length; i++) {
        var idx = elt[i];
        eltValue[idx] = id$(p_div + elt[i]).value;

    }
    if (eltValue['amount_min'] == "") eltValue["amount_min"] = 0;
    if (eltValue['amount_max'] == "") eltValue["amount_max"] = 0;

    //ledger's list r_jrn
    if (eltValue['nb_jrn'] > 0) {
        eltValue['r_jrn'] = [];
        for (i = 0; i < eltValue['nb_jrn']; i++) {
            var idx = p_div + 'r_jrn[' + i + ']';
            eltValue['r_jrn' + i] = id$(idx).value

        }
    }
    //ledger's tags
    var aTag = Array.from(document.getElementsByName(p_div + "tag[]"));
    eltValue["tag[]"] = [];
    for (i = 0; i < aTag.length; i++) {
        eltValue["tag[]"][i] = aTag[i].value;
    }
    new Ajax.Request('ajax_misc.php', {
        method: "POST",
        parameters: eltValue,
        onSuccess: function (req) {
            try {
                var answer = req.responseJSON;
                if (answer.status == 'OK') {
                    /*Add the new list to the selection */
                    var new_item = document.createElement('li');
                    new_item.innerHTML = answer.filter_name;
                    new_item.setAttribute("id", "manageli" + p_div + "_" + answer.filter_id);
                    id$('manage' + p_div).appendChild(new_item);
                    id$(p_div + "filter_new").value = "";
                } else {
                    throw answer.message;
                }
            } catch (e) {
                smoke.alert(e);
            }
        }
    });
}

/**
 * Load a search filter  and fill up the form search
 * @param p_div prefix id of all concerned DOM Element
 * @param p_dossier
 * @param p_filter_id filter id (SQL user_filter.id)
 * @see Acc_Ledger_Search
 */
function load_filter(p_div, p_dossier, p_filter_id) {
    new Ajax.Request('ajax_misc.php', {
        method: "get",
        parameters: {"gDossier": p_dossier, "div": p_div, "op": "load_filter", "filter_id": p_filter_id},
        onSuccess: function (req) {
            try {
                var answer = req.responseJSON;
                var elt = ['ledger_type', 'date_start', 'date_end', 'date_paid_start', 'date_paid_end',
                    'desc', 'amount_min', 'amount_max', 'qcode', 'accounting', 'operation_filter', 'tag_option'
                    , 'p_currency_code', 'tva_id_search'];
                for (var i = 0; i < elt.length; i++) {
                    var idx = elt[i];
                    id$(p_div + idx).value = answer[elt[i]];
                }
                // fillup the r_jrn array
                var eltLedgerId = id$("ledger_id" + p_div);
                eltLedgerId.innerHTML = "";
                var eltHidden = document.createElement("input");
                eltHidden.setAttribute("name", p_div + "nb_jrn");
                eltHidden.setAttribute("type", "hidden");
                eltHidden.setAttribute("id", p_div + "nb_jrn");
                eltHidden.setAttribute("value", answer.nb_jrn);
                eltLedgerId.appendChild(eltHidden);

                for (var i = 0; i < answer.nb_jrn; i++) {
                    // create hidden element and add them into eltLedgerId
                    var eltHidden = document.createElement("input");

                    eltHidden.setAttribute("name", p_div + "r_jrn[" + i + "]");
                    eltHidden.setAttribute("type", "hidden");
                    eltHidden.setAttribute("id", p_div + "r_jrn[" + i + "]");
                    eltHidden.setAttribute("value", answer.r_jrn[i]);
                    eltLedgerId.appendChild(eltHidden);
                }
                new Ajax.Request("ajax_misc.php", {
                    method: "get",
                    parameters: {
                        "gDossier": p_dossier, "div": p_div, "op": "display_filter_tag", "filter_id": p_filter_id,
                        uf_tag: answer.uf_tag
                    },
                    onSuccess: function (req) {
                        id$(p_div + 'tag_choose_td').update(req.responseText);
                    }
                })


            } catch (e) {
                smoke.alert(e.message);
            }

        }
    });
}

/**
 *  delete a saved search filter  from the db, it is limited to the current
 * user
 * @parameter p_div
 identification des elements LI manageli{div}_{filter_id}
 identification element UL manage{div}
 @parameter p_filter_id SQL user_filter.id
 */

function delete_filter(p_div, p_dossier, p_filter_id) {
    new Ajax.Request("ajax_misc.php", {
        parameters: {"gDossier": p_dossier, "div": p_div, "filter_id": p_filter_id, 'op': "delete_search_operation"},
        method: "POST",
        onSuccess: function (req) {
            try {
                var answer = req.evalJSON;

                var child = id$("manageli" + p_div + "_" + p_filter_id);
                if (child) {
                    id$("manage" + p_div).removeChild(child);
                }
            } catch (e) {
                console.log(e.message)
            }

        }
    })

}

/**
 * Reset the search_form and reinitialize all the input but ledger_type
 * @param p_div prefix for DOM Element
 */
function reset_filter(p_div) {
    // clean all the input fields but ledger_type remains
    var elt = ['date_start', 'date_end', 'date_paid_start', 'date_paid_end', 'desc', 'amount_min', 'amount_max', 'qcode', 'accounting', 'tva_id_search'];
    for (var i = 0; i < elt.length; i++) {
        var idx = elt[i];
        id$(p_div + idx).value = "";
    }
    if (document.getElementById(p_div + "date_start_hidden")) {
        id$(p_div + "date_start").value = id$(p_div + "date_start_hidden").value;
    }
    if (document.getElementById(p_div + "date_end_hidden")) {
        id$(p_div + "date_end").value = id$(p_div + "date_end_hidden").value;
    }
    // clean all the selected ledger
    var eltLedgerId = id$("ledger_id" + p_div);
    eltLedgerId.innerHTML = "";
    var eltHidden = document.createElement("input");
    eltHidden.setAttribute("name", p_div + "nb_jrn");
    eltHidden.setAttribute("type", "hidden");
    eltHidden.setAttribute("id", p_div + "nb_jrn");
    eltHidden.setAttribute("value", 0);
    eltLedgerId.appendChild(eltHidden);

    // By default , unpaid is uncked
    id$(p_div + "operation_filter").value = "all";
}
function display_list_filter(p_dossier,access_code,ledger_type)
{
    new Ajax.Request("ajax_misc.php",{
        parameters:{"gDossier":p_dossier
            ,"op":"display_list_filter"
            ,"ac":access_code
            ,'ledger_type':ledger_type
        },
        method:'GET',
        onSuccess: function (responseHtml) {
            try {
                var posy=calcy(250)
                var div = create_div({"id":"display_list_filter_div",
                    'cssclass': "inner_box2", 'style': 'right:5%;top:'+posy+"px"});
                div.update(responseHtml.responseText);
                div.show();
            }catch (e) {
                console.error(e.message);
            }
        }
    })
}

/**
 * propose to duplicate an operation
 */
function duplicate_operation(p_dossier, p_jr_id) {
    waiting_box();
    var duplicate_div = create_div({id: "duplicate_operation_div", cssclass: "inner_box"});

    new Ajax.Request("ajax_misc.php", {
            parameters: {
                "op": "ledger",
                "gDossier": p_dossier,
                "jr_id": p_jr_id,
                "act": "duplicateop",
                "div": "duplicate_operation_div"
            },
            onSuccess: function (req) {
                remove_waiting_box();
                var xml = req.responseXML;

                if (xml.getElementsByTagName("ctl").length == 0) {
                    console.log("erreur" + req.responseText);
                }
                add_div(duplicate_div);

                duplicate_div.setStyle({
                    "position": "fixed", "top": "15%", "z-index": get_next_layer(),
                    "min-width": "30rem",
                    "left": "30%",
                    "width": "40%"
                });
                duplicate_div.innerHTML = getNodeText(xml.getElementsByTagName("code")[0]);
                duplicate_div.setStyle({display: "block"});
            }
        }
    );
}
/**
 * Go to detail of Tax for a specific period , ledger id and tva_id
 */

function tax_detail_view (dossier_id,date_from,date_to,nLedger_id,nTva_id)
{
	try
		{
	        var dgbox="detail_tax_box";
	        waiting_box();
	        removeDiv(dgbox);
	        // For form , most of the parameters are in the FORM
	        // method is then POST
	         //var queryString=id$(p_form_id).serialize(true);

	       var queryString = {
	                op: 'tax_detail',
	                act: "tax_detail_view",
	                gDossier: dossier_id,
                    boxid: dgbox,
	                date_from:date_from,
                    date_to:date_to,
                    ledger_id:nLedger_id,
                    tva_id:nTva_id
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
							var y=calcy(15);
							var div_style="position:absolute;"+";top:"+y+"px"+";z-index:"+get_next_layer();
							add_div({id:dgbox,cssclass:'inner_box',html:loading(),style:div_style,drag:true});
							id$(dgbox).update(req.responseText);

					      }
					  }
	              );
		}catch( e)
		{
			alert_box(e.message);
		}
}
/**
 * For operation_exercice let update periode when changing folder
 * @type {{update_periode: operation_exercice.update_periode}}
 */
var operation_exercice = {
    update_periode: function (dossier_id) {
        try {
            waiting_box();
            var queryString = {
                op: 'operation_exercice+update_periode',
                folder: id$('dos_id').value,
                gDossier: dossier_id
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

                        id$("select_exercice_id").update(req.responseText);

                    }
                }
            );
        } catch (e) {
            console.error('oe-update_periode', e.message);
        }
    },
    modify_row: function (row_operation_exercice, oe_id) {
        try {
            var dgbox = "operation_exercice_bx";
            waiting_box();
            removeDiv(dgbox);
            // For form , most of the parameters are in the FORM
            // method is then POST
            //var queryString=id$(p_form_id).serialize(true);
            var queryString = {
                op: 'operation_exercice+modify_row',
                oe_id: oe_id,
                row_id: row_operation_exercice,
                gDossier: id$('gDossier').value
            };
            var action = new Ajax.Request(
                "ajax_misc.php",
                {
                    method: 'POST',
                    parameters: queryString,
                    onFailure: ajax_misc_failure,
                    onSuccess: function (req) {
                        remove_waiting_box();
                        if (req.responseText == 'NOCONX') {
                            reconnect();
                            return;
                        }
                        var y = calcy(15);
                        var div_style = "position:absolute;" + ";top:" + y + "px"+";z-index:"+get_next_layer();
                        add_div({id: dgbox, cssclass: 'inner_box', html: loading(), style: div_style, drag: true});
                        id$(dgbox).update(req.responseText);
                    }
                }
            );
        } catch (e) {
            console.error('oe-modify_row', e.message);
        }
    },
    /**
     * Save data from modify_row
     */
    save_row: function () {
        try {
            var dgbox = "operation_exercice_bx";
            waiting_box();

            // For form , most of the parameters are in the FORM
            // method is then POST
            //var queryString=id$(p_form_id).serialize(true);

            var queryString = id$('operation_exercice_input_row_frm').serialize(true);

            var action = new Ajax.Request(
                "ajax_misc.php",
                {
                    method: 'POST',
                    parameters: queryString,
                    onFailure: ajax_misc_failure,
                    onSuccess: function (req) {
                        remove_waiting_box();
                        if (req.responseText == 'NOCONX') {
                            reconnect();
                            return;
                        }

                        if (req.responseJSON['status'] == "OK") {
                            rowid = req.responseJSON['row_id'];
                            if (queryString['row_id'] == -1) {
                                var row = new Element("tr");
                                row.id = "oe_" + rowid;
                                row.setAttribute("oed_id", rowid);
                                row.setAttribute("oe_id", req.responseJSON['oe_id']);
                                row.update(req.responseJSON['content']);
                                id$("operation_exercice_tb").appendChild(row);
                                row.addEventListener("click", function (event) {
                                    operation_exercice.click_modify_row(row)
                                })

                            } else {
                                id$("oe_" + rowid).update(req.responseJSON['content']);
                            }
                            new Effect.Highlight("oe_" + req.responseJSON['row_id'], {
                                startcolor: '#FAD4D4',
                                endcolor: '#F78082'
                            });
                            operation_exercice.display_total(rowid)
                            id$(dgbox).remove();
                            return;

                        }
                        id$(dgbox).update(req.responseText);

                    }
                }
            );
        } catch (e) {
            console.error('oe-save_row' + e.message);
        }
    },
    /**
     * display the balance
     */
    display_total: function (row_id) {
        try {
            var dgbox = "tot_ope_exe";
            var queryString = {
                op: 'operation_exercice+display_total',
                row_id: row_id,
                gDossier: id$('gDossier').value
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

                        id$(dgbox).update(req.responseText);

                    }
                }
            );
        } catch (e) {
            console.error('oe-display_total' + e.message);
        }
    },
    /**
     * delete a row
     */
    delete_row: function (row_id) {
        try {
            var queryString = {
                op: 'operation_exercice+delete_row',
                row_id: row_id,
                gDossier: id$('gDossier').value
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
                        if (req.responseJSON["row_id"] != "") {
                            operation_exercice.display_total(req.responseJSON["row_id"])
                        }
                        id$('oe_' + queryString['row_id']).remove();
                        id$('operation_exercice_bx').remove();
                    }
                }
            );
        } catch (e) {
            alert_box("oe+delete_row", e.message);
        }
    },
    click_modify_row: function (item) {
        operation_exercice.modify_row(item.getAttribute("oed_id"), item.getAttribute("oe_id"));
    },
    /**
     * check and transfer if it is good
     */
    transfer: function () {
        try {
            var dgbox = "oe_transfer_div";
            waiting_box();

            var queryString = id$("operation_exercice_transfer_frm").serialize(true);
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
                        var answer=req.responseJSON;

                        id$('operation_exercice_transfer_info').update(answer.content);


                    }
                }
            );
        } catch (e) {
            alert_box(e.message);
        }
    }
}


var Supplement_Document={
    
};
/**
 * see  ledger_detail_sup_files.php
 * $rowid=sprintf("row_js_%s_%s",$div,$item->js_id);
 * @param {int} nDossier
 * @param {string} sDiv
 * @param {int} nJS_ID
 * @returns {void}
 */
Supplement_Document.delete_document=function (nDossier,sDiv,nJS_ID,nJR_ID)
{
    confirm_box(null
                ,content[47]
                ,function (){
                    try
                    {
                        var queryString = {
                            gDossier:nDossier,
                            div:sDiv,
                            jr_id:nJR_ID,
                            js_id:nJS_ID,
                            op:"ledger",
                            act:"rmsup"
                        };
                        var action = new Ajax.Request(
                            "ajax_misc.php",
                            {
                                method: 'POST',
                                parameters: queryString,
                                onSuccess: function (req) {
                                    remove_waiting_box();
                                    if (req.responseText == 'NOCONX') {
                                        reconnect();
                                        return;
                                    }
                                    id$("row_js_"+sDiv+"_"+nJS_ID).remove();

                                }
                            }
                        );
                    } catch (e)
                    {
                        alert_box(e.message);
                    }
                }
    );

}
Supplement_Document.input_file=function(nDossier,sDiv,nJR_ID)
{
    try
    {
        var dgbox = "sup_doc_input_file"+sDiv;
        waiting_box();
        removeDiv(dgbox);
        var queryString = {
            gDossier:nDossier,
            div:sDiv,
            jr_id:nJR_ID,
            op:"ledger",
            act:"input_file",
            dgbox:dgbox
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
                        var y = calcy(15);
                        var div_style = "position:absolute;" + ";top:" + y + "px"+";z-index:"+get_next_layer();
                        add_div({id: dgbox, cssclass: 'inner_box2', html: loading(), style: div_style, drag: true});
                        $(dgbox).innerHTML = req.responseText;
                        
                    }
                }
        );
    } catch (e)
    {
        alert_box(e.message);
    }

}
/**
 * 
 * @param {string} FORM ID
 * @returns {Boolean}
 */
Supplement_Document.save_file=function(form_dom_id)
{
    try 
    {
        waiting_box();
        var form_data=$(form_dom_id).serialize();
        var xhr = new XMLHttpRequest();
        var div=id$(form_dom_id).elements["div"].value;
        // check size
        var total_size=0;
        var file_to_upload=id$("doc_sup");
        var max_size=id$(form_dom_id).elements["MAX_FILE_SIZE"].value;
        var post_max_size=id$(form_dom_id).elements["post_max_size"].value;
        var feedback_div=id$('feedback'+div);
        
        for (var e=0;e<file_to_upload.files.length;e++) {

            // check the size
            if (file_to_upload.files[e].size > max_size) {
                // if size > accepted size , push filename with error in an array feedback,
                feedback_div.innerHTML += '<p class="notice">' + file_to_upload.files[e].name 
                                        +content[78]+ "</p>";
                remove_waiting_box();
                return false;
            } else if ( total_size+file_to_upload.files[e].size >= post_max_size )
            {
                 feedback_div.innerHTML += '<p > limite '+content[78]+" </p>";
                 remove_waiting_box();
                 return false;
            }
            else {
                total_size+=file_to_upload.files[e].size;
            }
        }
        feedback_div.innerHTML="loading....";
        document.getElementById("progress_upload1b").setAttribute("value", 0);

        xhr.upload.onprogress = function (e) {
            document.getElementById("progress_upload1b").setAttribute("max", e.total) + "<br/>";
            document.getElementById("progress_upload1b").setAttribute("value", e.loaded) + "<br/>";

        }

        xhr.onreadystatechange = function (event) {
            if (this.readyState == XMLHttpRequest.DONE)
            {
                remove_waiting_box();

                if (this.status === 200) {
                        document.getElementById("supplement_div_list"+div).innerHTML += this.responseText ;
                        let dgbox=id$(form_dom_id).elements["dgbox"].value;
                        removeDiv(dgbox);
                        
                    } else {
                        document.getElementById("supplement_div_list"+div).innerHTML += "status" + this.statusText + "<br/>";
                    }
                }
            }
        
        xhr.open("POST", "ajax_misc.php?"+form_data, true);
     // works xhr.send(new FormData(input.parentElement));
        var formData = new FormData(document.getElementById(form_dom_id));
        xhr.send(formData);
    }catch(e)
    {
        console.error("Supplement_Document.save_file "+e.message)
    }
    remove_waiting_box();
    return false;
}
