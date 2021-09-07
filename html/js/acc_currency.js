/* 
 * Copyright (C) 2018 Dany De Bontridder <dany@alchimerys.be>
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
 * @file 
 * @brief All the currency related ajax calls
 */

/**
 * Delete a old currency rate via ajax, but all currency must have at least one rate, delete the rate and hide the
 * row in the table "currency_rate_table"
 * @param {int} p_dossier
 * @param {int} p_id
 * @see currency_mtable_input.php
 * DOMID 
 *   -  row = currency_rate_{p_id}
 *   - table = currency_rate_table
 */
function CurrencyRateDelete(p_dossier, p_id)
{
    smoke.confirm("Confirm ?", function (e) {
        if (e) {
            waiting_box();
            var a = new Ajax.Request("ajax_misc.php", {
                method: 'get',
                parameters:{gDossier:p_dossier,op:"CurrencyRateDelete",currency_rate_id:p_id},
                onSuccess: function (req)
                {
                    remove_waiting_box();
                    var answer=req.responseText.evalJSON();
                    if ( answer['status'] == 'NOK') {
                        smoke.alert(answer['content']);
                    } else {
                        $('currency_rate_'+p_id).hide();
                        alternate_row_color("currency_rate_table");
                    }
                }
            });
        }
    });

}
/**
 * Update the field p_update with the rate from the currency
 * @param DOMID p_code where the cr_code_iso will be selected
 * @param DOMID p_update element to update
 */
function CurrencyUpdateValue(p_dossier,p_code,p_update)
{
    new Ajax.Request("ajax_misc.php",{
        method:"get",
        asynchronous:false,
        parameters:{p_code:$(p_code).value,gDossier:p_dossier,op:'CurrencyRate'},
        onSuccess:function (req) {
            var answer=req.responseText.evalJSON();
            if ( answer.status == "OK") {
                $(p_update).value=answer.content;
            } else {
                smoke.alert(answer.content);
            }
        }
    });
}
/**
 * Update the field p_update with the code ISO from the currency
 * @param DOMID p_code where the cr_code_iso will be selected
 * @param DOMID p_update element to update
 */
function CurrencyUpdateCode(p_dossier,p_code,p_update)
{
       new Ajax.Request("ajax_misc.php",{
        method:"get",
        parameters:{p_code:$(p_code).value,gDossier:p_dossier,op:'CurrencyCode'},
        onSuccess:function (req) {
            var answer=req.responseText.evalJSON();
            if ( answer.status == "OK") {
                $(p_update).innerHTML=answer.content;
            } else {
                smoke.alert(answer.content);
            }
        }
    });
}
/**
 * Update the field Update with the amount in EUR (= default currency), ledger sale or purchase
 * @param DOMID p_rate where the rate is stored
 * @param DOMID p_update element to update with the rate
 */
function CurrencyCompute(p_rate,p_update)
{
    var tvac=1;
    
    if ($('tvac')) {
        tvac=$('tvac').innerHTML;
    }
   
   if (  isNaN(tvac)) {
       tvac=1;
   }
   var rate=$(p_rate).value;
   if (  isNaN(rate)) {
       rate=1;
   }
   var tot=tvac/rate;
   tot=Math.round(tot*100)/100;
   $(p_update).innerHTML=tot;
    
}
/**
 * Update the field Update with the amount in EUR (= default currency) for Miscealleneous Operation
 *  amount for DEB (domid : default_currency_deb , totalDeb)
 *  amount for CRED ( domid = default_currency_cred , totalCred), 
 * @param DOMID p_rate where the rate is stored
 * @param DOMID p_update element to update with the rate
 */
function CurrencyComputeMisc(p_rate,p_update)
{
   var debAmount=$('totalDeb').innerHTML;
   var credAmount=$('totalCred').innerHTML;
   
   if (  isNaN(debAmount)) {
       debAmount=0;
   }
   if (  isNaN(credAmount)) {
       credAmount=0;
   }
   var rate=$(p_rate).value;
   if (  isNaN(rate) || parseFloat(rate) == 0) {
       rate=1;
   }
   var totDeb=debAmount/rate;
   totDeb=Math.round(totDeb*100)/100;
   $('default_currency_deb').innerHTML=totDeb;
   
   var totCred=credAmount/rate;
   totCred=Math.round(totCred*100)/100;
   $('default_currency_cred').innerHTML=totCred;
    
}

/**
 * Update the screen of input for Purchase and Sale
 * @param {type} p_dossier 
 * @param {type} p_code name of the SELECT containing the currency code
 * @param {type} p_update Domid of the element to update
 * @param {type} p_rate  domid of the element containing the currency rate
 * @param {type} p_eur_amount domid of the amount in default currency to update
 * @returns {undefined}
 */
function LedgerCurrencyUpdate(p_dossier,p_code,p_update,p_rate,p_eur_amount)
{
    // Hide or show the row of the table with the amount in EUR
    if ($(p_code).value != 0) {
        $('row_currency').show();
    }else {
        $('row_currency').hide();
    }
    CurrencyUpdateValue(p_dossier,p_code,p_rate);
    CurrencyUpdateCode(p_dossier,p_code,p_update);
    // Compute all the fields
    compute_all_ledger 	();

}
/**
 * Update the screen of input for Misc. Operation
 * @param {type} p_dossier 
 * @param {type} p_code name of the SELECT containing the currency code
 * @param {type} p_update Domid of the element to update
 * @param {type} p_rate  domid of the element containing the currency rate
 * @param {type} p_eur_amount domid of the amount in default currency to update
 * @returns {undefined}
 */
function LedgerCurrencyUpdateMisc(p_dossier,p_code,p_update,p_rate,p_eur_amount)
{
    // Hide or show the row of the table with the amount in EUR (= default currency)
    if ($(p_code).value != -1) {
        $('row_currency').show();
    }else {
        $('row_currency').hide();
    }
    CurrencyUpdateValue(p_dossier,p_code,p_rate);
    CurrencyUpdateCode(p_dossier,p_code,p_update);
    // Compute all the fields
    checkTotalDirect();

}