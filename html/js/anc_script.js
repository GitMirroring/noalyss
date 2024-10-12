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
 *  javascript for the analytic accountancy
 */

/**
 *  add a row for the CA
 * \param p_table_id
 * \param p_amount amount to reach
 */
function add_row(p_table, p_seq)
{
    var mytable = id$(p_table).tBodies[0];
  if (!mytable)
    {
        return;
    }
   var a_amount=document.getElementsByClassName(p_table+'-amount');
      var max=0;
      for (var e=0;e<a_amount.length;e++) {
        max +=Math.abs( parseFloat(a_amount[e].value));
        e=a_amount.length+1;
      }

    if (mytable.rows.length > 15)
    {
        alert_box(content[55]);
        return;
    }
    var amount = compute_total_table(p_table, p_seq);
    if (max < amount)
    {
        alert_box(content[48]+' '+content[56]+ ' '+max + content[57]+' '+ amount);
        
    }
    // For the detail view (modify_op) there is several form and then several time the
    // element
    var rowToCopy = mytable.rows[1];
    var row = mytable.insertRow(mytable.rows.length);

    for (var i = 0; i < rowToCopy.cells.length; i++)
    {
        var cell = row.insertCell(i);
        var txt = rowToCopy.cells[i].innerHTML;
        cell.innerHTML = txt;
    }
    var col = document.getElementsByName("val[" + p_seq + "][]");
    let tmp_amount=max - amount;
    col[col.length - 1].value = Math.round(tmp_amount*100)/100;
    anc_refresh_remain(p_table, p_seq);
}
/**
 *Compute total of a form from Anc_Operation::display_form_plan
 *@param {dom_id} p_table table id
 *@param {int}seq sequence of the line
 *@see Anc_Operation::display_form_plan
 */
function compute_total_table(p_table, seq)
{
    try {

        var i = 0;
        var tot = 0;
        var col = document.getElementsByClassName(p_table+'-value-'+seq);
        var rounded_value = 0 ;
        for (i = 0; i < col.length; i++)
        {
          
                rounded_value = Math.round(parseFloat(col[i].value) *100);
                tot += rounded_value
          
        }
        return tot/100;
    }
    catch (e)
    {
        alert_box(e.message);
    }
}
/**
 * Refresh remain of account. analytic
 *@param p_table table id
 *@param p_seq sequence of the line
 *@see Anc_Operation::display_form_plan
 */
function anc_refresh_remain(p_table, p_seq)
{

    try
    {
      var a_amount=document.getElementsByClassName(p_table+'-amount');
      var tot_line=0;
      for (var e=0;e<a_amount.length;e++) {
        tot_line +=Math.abs( parseFloat(a_amount[e].value));
        e=a_amount.length+1;
      }
        
        var tot_table = compute_total_table(p_table, p_seq);
        var remain = tot_line - tot_table;

        remain = Math.round(remain * 100) / 100;
        id$('remain' + p_table).innerHTML = remain;
        if (remain == 0)
        {
            id$('remain' + p_table).style.color = "green"
        }
        else
        {
            id$('remain' + p_table).style.color = "red"
        }
    } catch (a)
    {
        alert_box(a.message);
    }
}
/**
 * Check the amount of the CA
 * @param p_style : error or ok, if ok show a ok box if the amount are equal
 *
 *
 * @returns true if the amounts are equal
 */
function verify_ca(div)
{
    try
    {

        var idx = 0;
        var amount_error = 0;
        // put a maximum
        while (idx < 50)
        {
            var table = div + 't' + idx;
            if (id$(table))
            {
                var total_amount = 0;
                // table is found compute the different val[]
                  var array_value = document.getElementsByClassName(table+'-value-'+idx);
                

                for (var i = 0; i < array_value.length; i++)
                {
                    if (isNaN(array_value[i].value))
                    {
                        array_value[i].value = 0;
                    }

                    total_amount += parseFloat(array_value[i].value);
                }
                var a_amount=document.getElementsByClassName(table+'-amount');
                var amount=0;
                for (var e=0;e<a_amount.length;e++) {
                  amount +=Math.abs( parseFloat(a_amount[e].value));
                  e=a_amount.length+1;
                }
                if (isNaN(amount)) {
                    amount=0;
                } else {
                    amount = Math.round(amount*100)/100;
                }
                var diff = amount*100 - total_amount*100;
                if (Math.round(diff/100) != 0.0)
                {
                    id$(table).style.backgroundColor = 'red';
                    amount_error++;
                }
                else
                {
                    id$(table).style.backgroundColor = 'lightgreen';

                }
                idx++;
            }
            else
                break;
        }
        if (amount_error != 0)
        {
            alert_box(content[54]);
            return false;
        }
        return true;
    }
    catch (e)
    {
        alert_box(e.message);
        return false;
    }
}
/***
 *  open a window for searching a CA account,
 * @param p_dossier dossier id
 * @param  p_target ctrl to update
 * @param  p_source ctrl containing the pa_id
 */
function search_ca(p_dossier, p_target, p_source)
{
    var pa_id = id$(p_source).value;
    waiting_box();
    removeDiv('search_anc');
    var qs = "op=openancsearch&gDossier=" + p_dossier + "&ctl=searchanc";
    qs += "&c2=" + pa_id + "&c1=" + p_target;

    var action = new Ajax.Request('ajax_misc.php',
            {
                method: 'get',
                parameters: qs,
                onFailure: null,
                onSuccess: function(req) {
                    try {
                        remove_waiting_box();
                        var pos = fixed_position(250, 150) + ";width:30%;height:50%";
                        add_div({
                            id: "searchanc",
                            drag: 1,
                            cssclass: "inner_box",
                            style: pos
                        });
                        id$('searchanc').innerHTML = req.responseText;

                    } catch (e) {
                        alert_box(e.message);
                    }
                }
            }
    );

}
function search_anc_form(obj)
{
    var qs = "op=resultancsearch&ctl=searchanc&";
    var name = obj.id;
    qs += id$(name).serialize(false);
    waiting_box();
    var action = new Ajax.Request('ajax_misc.php',
            {
                method: 'get',
                parameters: qs,
                onFailure: null,
                onSuccess: function(req) {
                    try {
                        remove_waiting_box();
                        id$('searchanc').innerHTML = req.responseText;
                        req.responseText.evalScripts();

                    } catch (e) {
                        alert_box(e.message);
                    }
                }
            }
    );
    return false;
}
function caod_checkTotal()
{
    var ie4 = false;
    if (document.all)
    {
        ie4 = true;
    }// Ajouter getElementById par document.all[str]
    var total_deb = 0.0;
    var total_cred = 0.0;
    var nb_item = id$('nbrow').value;

    for (var i = 0; i < nb_item; i++)
    {
        var doc_amount = id$("pamount" + i);
        if (!doc_amount)
        {
            return;
        }
        var side = id$("pdeb" + i);
        if (!side)
        {
            return;
        }
        var amount = parseFloat(doc_amount.value);

        if (isNaN(amount) == true)
        {
            amount = 0.0;
        }
        amount = Math.round(amount*100)/100;
        if (side.checked == false)
        {
            total_cred += amount;
        }
        if (side.checked == true)
        {
            total_deb += amount;
        }
    }



    var r_total_cred = Math.round(total_cred * 100) / 100;
    var r_total_deb = Math.round(total_deb * 100) / 100;
    id$('totalDeb').innerHTML = r_total_deb;
    id$('totalCred').innerHTML = r_total_cred;

    if (r_total_deb != r_total_cred)
    {
        id$("totalDiff").style.color = "red";
        id$("totalDiff").style.fontWeight = "bold";
        id$("totalDiff").innerHTML = "Différence";
        var diff = total_deb - total_cred;
        diff = Math.round(diff * 100) / 100;
        id$("totalDiff").innerHTML = diff;

    }
    else
    {
        id$("totalDiff").innerHTML = "0.0";
    }
}

/**
 * remove an operation
 *@param p_dossier is the folder
 *@param p_oa_group is the group of the analytic operation
 */
function anc_remove_operation(p_dossier, p_oa_group)
{
     smoke.confirm(content[50]+"\n",
     function (a)
     {
         if ( a) {
            var obj = {"oa":
                        p_oa_group, "gDossier":
                        p_dossier, "op": "remove_anc"};
            var queryString = encodeJSON(obj);
            var e = new Ajax.Request("ajax_misc.php",
                    {method: 'get', parameters: queryString,onSuccess:function req() {
                            id$("tr"+p_oa_group).remove();
                    }
                    
             });
             
         } else
         {
             return;
         }
     });
}
/**
 * add a row in misc operation for ANC
 * the code must be adapted for that
 */
function anc_add_row(tableid)
{
    var style = 'class="input_text"';
    var mytable = id$(tableid).tBodies[0];
    var nNumberRow = mytable.rows.length;
    var oRow = mytable.insertRow(nNumberRow);
    var rowToCopy = mytable.rows[1];
    var nNumberCell = rowToCopy.cells.length;
    var nb = id$("nbrow");
    var oNewRow = mytable.insertRow(nNumberRow);
    for (var e = 0; e < nNumberCell; e++)
    {
        var newCell = oRow.insertCell(e);
        var tt = rowToCopy.cells[e].innerHTML;
        var new_tt = tt.replace(/pop0/g, "pop" + nb.value);
        var new_tt = tt.replace(/qcode0/g, "qcode" + nb.value);
        new_tt = new_tt.replace(/pamount0/g, "pamount" + nb.value);
        new_tt = new_tt.replace(/pdeb0/g, "pdeb" + nb.value);
        newCell.innerHTML = new_tt;
        new_tt.evalScripts();
    }
    id$("pamount" + nb.value).value = "0";
    nb.value++;
}
/**
 * this function is called before the querystring is send to the
 * fid2.php, add a filter based on the ledger 'p_jrn'
 *@param obj is the input field
 *@param queryString is the queryString to modify
 *@see ICard::input
 */
function filter_anc(obj, queryString)
{
    var pa_id = obj.plan_ctl;
    queryString = queryString + "&pa_id=" + pa_id;
    return queryString;
}
/**
 *  compute and display Analytic activity, related to the choosen distribution key
 * @param p_dossier is the dossier id
 * @param p_table is table id to replace
 * @param p_amount is the amount to distribute
 * @param p_key_id is the choosen key
 * 
 */
function anc_key_compute(p_dossier, p_table, p_amount, p_key_id)
{
    waiting_box();
    var op = "op=anc_key_compute";
    var queryString = op + "&gDossier=" + p_dossier + "&t=" + p_table + "&amount=" + p_amount + '&key=' + p_key_id;
    try {
        var action = new Ajax.Request(
                "ajax_misc.php",
                {
                    method: 'get',
                    parameters: queryString,
                    onFailure: error_box,
                    onSuccess: function(req, json) {
                        try
                        {
                            var name_ctl = p_table;
                            var answer = req.responseXML;
                            remove_waiting_box();
                            var html = answer.getElementsByTagName('code');
                            if (html.length == 0) {
                                var rec = req.responseText;
                                alert_box('erreur :' + rec);
                            }

                            var code_html = getNodeText(html[0]); // Firefox ne prend que les 4096 car.
                            code_html = unescape_xml(code_html);
                            id$(name_ctl).innerHTML = code_html;
                            code_html.evalScripts();
                            removeDiv('div_anc_key_choice');
                        } catch (e)
                        {
                            error_message(e.message);
                        }
                    }
                }

        );
    } catch (e) {
        error_message(e.message);
    }
}
/**
 *  choose the distribution key
 * in ajax, a window let you choose what key you want to use
 * 
 * @param p_dossier is the dossier
 * @param p_table the table id of the target
 * @param p_amount amount to distribute
 * @param p_ledger
 */
function anc_key_choice(p_dossier, p_table, p_amount,p_ledger)
{
    waiting_box();
    var op = 'op=anc_key_choice';
    var queryString = op + "&gDossier=" + p_dossier + "&t=" + p_table + "&amount=" + p_amount;
    try {
        queryString+='&led='+p_ledger;
        var action = new Ajax.Request(
                "ajax_misc.php",
                {
                    method: 'get',
                    parameters: queryString,
                    onFailure: error_box,
                    onSuccess: function(req, json) {
                        try
                        {
                            var name_ctl = 'div_anc_key_choice';
                            var answer = req.responseXML;
                            remove_waiting_box();
                            var html = answer.getElementsByTagName('code');
                            if (html.length == 0) {
                                var rec = req.responseText;
                                alert_box('erreur :' + rec);
                            }

                            var code_html = getNodeText(html[0]); // Firefox ne prend que les 4096 car.
                            code_html = unescape_xml(code_html);
                            var position=fixed_position(50,120);
                            add_div({id: name_ctl, cssclass: 'inner_box', style: position, drag: 1});
                            id$(name_ctl).innerHTML = code_html;
                        } catch (e)
                        {
                            error_message(e.message);
                        }
                    }
                }

        );

    } catch (e) {
        error_message(e.message);
    }
}

/**
 *  reset all the details of a row
 * @param p_dossier is the dossier
 * @param p_table the table id of the target
 * @param p_amount amount to distribute
 * @param p_ledger ledger id
 * @param p_jrnx_id jrnx.id
 */
function anc_key_clean(p_dossier, p_table, p_amount,p_ledger,p_jrnx_id,p_sequence)
{
    waiting_box();
    var op = 'op=anc_key_clean';
    var queryString = op + "&gDossier=" + p_dossier + "&t=" + p_table + "&amount=" + p_amount+"&jrnx_id="+p_jrnx_id+'&p_seq='+p_sequence;
    try {
        queryString+='&led='+p_ledger;
        var action = new Ajax.Request(
                "ajax_misc.php",
                {
                    method: 'get',
                    parameters: queryString,
                    onFailure: error_box,
                    onSuccess: function(req, json) {
                        try
                        {
                            var answer = req.responseXML;
                            remove_waiting_box();
                            var html = answer.getElementsByTagName('code');
                            if (html.length == 0) {
                                var rec = req.responseText;
                                alert_box('erreur :' + rec);
                            }

                            var code_html = getNodeText(html[0]); // Firefox ne prend que les 4096 car.
                            code_html = unescape_xml(code_html);
                            id$(p_table+"t"+p_sequence).innerHTML=code_html;
                        } catch (e)
                        {
                            error_message(e.message);
                        }
                    }
                }

        );

    } catch (e) {
        error_message(e.message);
    }
}
/**
 * Add a row for distribution key.
 * This function add a row in the table key distribution
 * @param p_table table id
 */
function add_row_key(p_table)
{
    var mytable = id$(p_table).tBodies[0];
    if (!mytable)
    {
        return;
    }
    var table_length=mytable.rows.length ;
    if ( table_length > 15)
    {
        alert_box("Maximum 15 lignes ");
        return;
    }
    var rowToCopy = mytable.rows[1];
    var row = mytable.insertRow(table_length);
    var nb=mytable.rows.length -2;
    for (var i = 0; i < rowToCopy.cells.length; i++)
    {
        var cell = row.insertCell(i);
        cell.className=rowToCopy.cells[i].className;
        var txt = rowToCopy.cells[i].innerHTML;
        if (  i == 0 )
        {
            var change=nb+1;
            cell.innerHTML =change+'<input id="row[]" type="hidden" value="-1" name="row[]">';
        } 
        else
        {
            if (i == rowToCopy.cells.length -1 )  {
                txt=txt.replace(/value="[0-9]*.{1}[0-9]*"/,'value="0"')
            } else {
                txt=txt.replace(/po_id\[0\]/g,'po_id['+nb+']');
            }
           cell.innerHTML = txt;
        }
    }
    id$('total_key').innerHTML="?";
}
function anc_key_compute_table()
{
    var tot=0;
    var i=0;
    var value=0;
    var percent=document.getElementsByName('percent[]');
    for (i=0;i<percent.length;i++)
    {
        value=percent[i].value;
        if ( value == 'undefined')
        {
            value=0;
        }
        if ( isNaN(value)) {
            value=0;
        }
        tot=tot+Math.round(value*100)/100;
    }
    id$('total_key').innerHTML=Math.round(tot*100)/100;

}

function anc_detail_op(p_oa_group,gDossier) {
    waiting_box();
    // create div
    new Ajax.Request ("ajax_misc.php",{
                        method:"get",
                        parameters:{"gDossier":gDossier,"op":"anc_detail_op","oa_group":p_oa_group},
                        onSuccess:function (req) {
                            add_div({"id":"anc_detail_op_div","cssclass":"inner_box","style":"position:fixed;top:5%"});
                            id$('anc_detail_op_div').update(req.responseText);
                            remove_waiting_box();
                        }
                    });
}
