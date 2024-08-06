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
 *  This file permit to use the AJAX function to fill up
 *        info from fiche
 *
 */

/**
 *  clean the row (the label, price and vat)
 * @param p_ctl the calling ctrl
 */
function clean_Fid(p_ctl)
{
    nSell=p_ctl+"_price";
    nTvaAmount=p_ctl+"_tva_amount";
    nBuy=p_ctl+"_price";
    nTva_id=p_ctl+"_tva_id";
    if ( $(nSell) )
    {
        $(nSell).value="";
    }
    if ( $(nBuy) )
    {
        $(nBuy).value="";
    }
    if ( $(nTva_id) )
    {
        $(nTva_id).value="-1";
    }
    if ( $(nTvaAmount))
    {
        $(nTvaAmount).value=0;
    }
}
function errorFid(request,json)
{
    alert_box('ERRFID'+content[48]);
}
/**
 *  this function fills the data from fid.php,
 * @param {object} p_ctl  : field of the input,
 *  possible object member
 * - label field to update with the card's name
 * - price field to update with the card's price
 * - tvaid field to update with the card's tva_id
 * - jrn field to force the ledger
  *@see successFid errorFid fid.php
 */
function ajaxFid(p_ctl)
{
	try
	{
	var gDossier=$('gDossier').value;
    var jrn=$(p_ctl).jrn;
    $(p_ctl).value=$(p_ctl).value.toUpperCase();
    if ( jrn == undefined )
    {
        if ($('p_jrn')!=undefined)
        {
            jrn=$('p_jrn').value;
        }
    }
    if ( jrn == undefined )
    {
        jrn=-1;
    }
    if ( trim($(p_ctl).value)=="" )
    {
        nLabel=$(p_ctl).label;
        if ($(nLabel) )
        {
            $(nLabel).value="";
            $(nLabel).innerHTML="&nbsp;";
            clean_Fid(p_ctl);
            return;
        }
    }
    var queryString="FID="+trim($(p_ctl).value);
    if ( $(p_ctl).label)
    {
        queryString+='&l='+$(p_ctl).label;
    }
    if ( $(p_ctl).tvaid)
    {
        queryString+='&t='+$(p_ctl).tvaid;
    }
    if ( $(p_ctl).price)
    {
        queryString+='&p='+$(p_ctl).price;
    }
    if ( $(p_ctl).purchase)
    {
        queryString+='&b='+$(p_ctl).purchase;
    }
    if ( $(p_ctl).typecard)
    {
        queryString+='&d='+$(p_ctl).typecard;
    }
    queryString=queryString+"&j="+jrn+'&gDossier='+gDossier;
    queryString=queryString+'&ctl='+p_ctl.id;
    queryString=encodeURI(queryString);

    var action=new Ajax.Request (
                   "fid.php",
                   {
                   method:'get',
                   parameters:queryString,
                   onFailure:errorFid,
                   onSuccess:successFid
                   }

               );
	}catch (e)  {
		alert_box(e.message);
		alert_box(p_ctl);
	}

}
/**
 callback function for ajax
 * @param request : object request
 * @param json : json answer
@verbatim
 {"answer":"ok",
 "flabel":"none",
 "name":"Chambre de commerce",
 "ftva_id":"none",
 "tva_id":" ",
 "fPrice_sale":"none",
 "sell":" ",
 "fPrice_purchase":"none",
 "buy":" "}
@endverbatim
 */
function successFid(request,json)
{
    if (request.responseText === 'NOCONX') { reconnect();return;}
    
    var answer=request.responseText.evalJSON(true);
    var flabel=answer.flabel;
    if ( answer.answer=='nok' )
    {
        set_value(flabel," Fiche inexistante");
        return;
    }

    var ftva_id=answer.ftva_id;
    var fsale=answer.fPrice_sale;
    var fpurchase=answer.fPrice_purchase;

    if ( ftva_id != 'none')
    {
        set_value(ftva_id,answer.tva_id);
    }
    if ( flabel != 'none')
    {
        set_value(flabel,answer.name);
    }
    if ( fsale != 'none')
    {
        set_value(fsale,answer.sell);
    }
    if ( fpurchase != 'none')
    {
        set_value(fpurchase,answer.buy);
    }


}
function ajax_error_saldo(request,json)
{
    alert_box('ERRSAL'+content[48]);
}
/*!\brief this function get the saldo
 * \param p_ctl the ctrl where we take the quick_code
 */
function ajax_saldo(p_ctl)
{
    var gDossier=$('gDossier').value;
    var ctl_value=trim($(p_ctl).value);
    var jrn=$('p_jrn').value;
    queryString="FID="+ctl_value+"&op=saldo";
    queryString=queryString+'&gDossier='+gDossier+'&j='+jrn;
    queryString=queryString+'&ctl='+ctl_value;
    /*  alert_box(queryString); */
    var action=new Ajax.Request (
                   "ajax_misc.php",
                   {
                   method:'get',
                   parameters:queryString,
                   onFailure:ajax_error_saldo,
                   onSuccess:ajax_success_saldo
                   }

               );

}
/*!\brief callback function for ajax
 * \param request : object request
 * \param json : json answer */
function ajax_success_saldo(request,json)
{
    var answer=request.responseText.evalJSON(true);
    $('first_sold').value=answer.saldo;

}

/**
 *  callback function for ajax_get when successuf
*/
function ajax_get_success(request,json)
{
    var answer=request.responseText.evalJSON(false);
    $(answer.ctl).show();
    $(answer.ctl).innerHTML=answer.html;
}
/*!\brief callback function for ajax_get when fails
*/
function ajax_get_failure(request,json)
{
    alert_box(content[53]);

}

var category_card={};

/**
 * Add an attribute selected in "sel"+p_object_name into the list (id:p_object_name+"_list")
 * this attribut will have the ID:p_object_name+"_elt"+ad_id (ad_id = attr_def.ad_id)
 * @param int p_dossier dossier nb
 * @param int p_fiche_def_ref is the frd_id
 * @param string p_object_name , name of the prefix for id 
 */
category_card.add_attribut=function (p_dossier,p_fiche_def_ref,p_object_name) {
    var select=$("sel"+p_object_name);
    var selected_attr=select.value;
    new Ajax.Request("ajax_misc.php",{
       method:"post",
       parameters:{"gDossier":p_dossier,
           "objname":p_object_name,
           "op":"template_cat_category",
           "action":"add_attribute",
           "frd_id":p_fiche_def_ref,
           "ad_id":selected_attr
       },
       onSuccess:function(req) {
           var answer=req.responseText.evalJSON();
           if ( answer.status == 'OK') {
               var newli=document.createElement("li")
               newli.setAttribute("id",p_object_name+"_elt"+selected_attr);
               newli.innerHTML=answer.content
               $(p_object_name+"_list").append(newli);
               select.remove(select.selectedIndex);
           } else {
               smoke.alert(answer.message);
           }
       }
    });
};
/**
 * Remove an attribute (id:p_object_name+"_elt"+ad_id (ad_id = attr_def.ad_id))
 * from the list (id:p_object_name+"list")
 * @param {int} p_dossier dossier nb
 * @param {string} p_object_name , name of the prefix for id
 * @param {int} p_fiche_def_ref is the frd_id
 * @param {type} p_attribute_id
 */
category_card.remove_attribut=function (p_dossier,p_fiche_def_ref,p_object_name,p_attribute_id) {
    new Ajax.Request("ajax_misc.php",{
       method:"post",
       parameters:{"gDossier":p_dossier,
           "objname":p_object_name,
           "op":"template_cat_category",
           "action":"remove_attribute",
           "frd_id":p_fiche_def_ref,
           "ad_id":p_attribute_id
       },
       onSuccess:function(req) {
           var answer=req.responseText.evalJSON();
           if ( answer.status == 'OK') {
               $(p_object_name+"_elt"+p_attribute_id).remove();
               var option=document.createElement("option");
               option.text=answer['content'];
               option.value=p_attribute_id;
               $('sel'+p_object_name).add(option);
           } else {
               smoke.alert(answer.message);
           }
       }
    });
};
/**
 * @brief ajax call to check a VAT number via VIES
 * @param p_domid  string domid of the IText
 * @see ivatnumber.class.php
 */
category_card.check_vatnumber=function(p_domid) {
    	try
    		{
    	        var dgbox="info"+p_domid;
    	        waiting_box();

    	        // For form , most of the parameters are in the FORM
    	        // method is then POST
    	         //var queryString=$(p_form_id).serialize(true);

    	       var queryString = {
    	                op: 'check_vatnumber',
    	                vatnr:$(p_domid).value,
    	                boxid: dgbox,
                        p_domid:p_domid
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
                                var answer=req.responseJSON;

                                if ( answer.status == 'OK')
                                {
                                    $(dgbox).update(answer.html);
                                    $(p_domid).value=answer.vat;
                                    $(p_domid).removeClassName("notice")
                                    $(p_domid).addClassName("valid")
                                } else {
                                    $(p_domid).addClassName("notice");
                                    $(p_domid).removeClassName("valid");
                                    $(dgbox).update(answer.html);
                                }

    					      }
    					  }
    	              );
    		}catch( e)
    		{
                remove_waiting_box();
    			console.error(e.message);
    		}
}
//-->

