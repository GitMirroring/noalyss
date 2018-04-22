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