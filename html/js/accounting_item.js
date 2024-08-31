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
/**
 * @file
 * manage accounting
 */

// Copyright Author Dany De Bontridder danydb@aevalys.eu
/**
 * show the popup for search an accounting item
 *@param object this, it must contains some attribute as
 * - jrn if set and different to 0, will filter the accounting item for a
 *   ledger
 * - account the tag which will contains the  number
 * - label the tag which will contains the label
 * - bracket if the value must be surrounded by [ ]
 * - acc_query for the initial query
 *@see ajax_poste.php
 */
function search_accounting(obj)
{
	var sx=0;
	if ( window.scrollY)
	{
            sx=window.scrollY+40;
	}
	else
	{
            sx=document.body.scrollTop+60;
	}

	var div_style="top:"+sx+"px";
	removeDiv('search_account');
	add_div({id:'search_account',cssclass:'inner_box',html:loading(),style:div_style,drag:false});

    var dossier=$('gDossier').value;

    var queryString="gDossier="+dossier;

    queryString+="&op2=sf";
    queryString+="&op=account";
    try
    {
        if ( obj.jrn)
        {
            queryString+="&j="+obj.jrn;
        }else {
            if ($("p_jrn")) {
                queryString+="&j="+$("p_jrn").value;
            }
        }
        if ( obj.account)
        {
            queryString+="&c="+obj.account;
        } 
        if ( obj.label)
        {
            queryString+="&l="+obj.label;
        }
        if ( obj.bracket)
        {
            queryString+="&b="+obj.bracket;
        }
        if( obj.noquery)
        {
            queryString+="&nq";
        }
        if( obj.no_overwrite)
        {
            queryString+="&nover";
        }
        if( obj.bracket)
        {
            queryString+="&bracket";
        }
        if ( ! obj.noquery)
        {
            if( obj.acc_query)
            {
                queryString+="&q="+obj.acc_query;
            }
            else
            {
                if ($(obj).account)
                {
                    var e=$(obj).account;
                    var str_account=$(e).value;
                    queryString+="&q="+str_account;
                }
            }
        }

        queryString+="&ctl="+'search_account';
        queryString=encodeURI(queryString);
        var action=new Ajax.Request ( 'ajax_misc.php',
                                      {
                                  method:'get',
                                  parameters:queryString,
                                  onFailure:errorPoste,
                                  onSuccess:result_poste_search
                                      }
                                    );
    }
    catch (e)
    {
        alert_box(e.message);
    }
}
/**
 * when you submit the form for searching a accounting item
 *@param obj form
 *@note the same as search_poste, except it answer to a FORM and not
 * to a click event
 */
function search_get_poste(obj)
{
    var dossier=$('gDossier').value;
    var queryString="gDossier="+dossier;

    queryString+="&op=account";
    queryString+="&op2=sf";

    if ( obj.elements['jrn'] )
    {
        queryString+="&j="+$F('jrn');
    }
    if ( obj.elements['account'])
    {
        queryString+="&c="+$F('account');
    }
    if ( obj.elements['label'])
    {
        queryString+="&l="+$F('label');
    }
    if( obj.elements['acc_query'])
    {
        queryString+="&q="+$F('acc_query');
    }
    if (obj.ctl )
    {
        queryString+="&ctl="+obj.ctl;
    }
    if( obj.elements['nosearch'])
    {
        queryString+="&nq";
    }
    if( obj.elements['nover'])
    {
        queryString+="&nover";
    }
    if( obj.elements['bracket'])
    {
        queryString+="&bracket";
    }

    $('asearch').innerHTML=loading();
    var action=new Ajax.Request ( 'ajax_misc.php',
                                  {
                                  method:'get',
                                  parameters:queryString,
                                  onFailure:errorPoste,
                                  onSuccess:result_poste_search
                                  }
                                );
}

/**
 * show the answer of ajax request
 *@param  answer in XML
 */
function result_poste_search(req)
{
    try
    {
        var answer=req.responseXML;
        var a=answer.getElementsByTagName('ctl');
        if ( a.length == 0 )
        {
            var rec=req.responseText;
            alert_box ('erreur :'+rec);
        }
        var html=answer.getElementsByTagName('code');

        var name_ctl=a[0].firstChild.nodeValue;
        var nodeXml=html[0];
        var code_html=getNodeText(nodeXml);
        code_html=unescape_xml(code_html);
        $('search_account').innerHTML=code_html;
    }
    catch (e)
    {
        alert_box(e.message);
    }
    try
    {
        code_html.evalScripts();
    }
    catch(e)
    {
        alert_box("RESPOSEAR"+content[48]+e.message);
    }

}
/**
* error for ajax
*/
function errorPoste()
{
    alert_box(content[53]);
}

/**
 * Display the list of card using a given accounting
 * @param dossier
 * @param accounting
 * 
 */
function display_all_card(p_dossier,p_accounting)
{
    waiting_box();
    var div_dest=add_div({id:'info_card_accounting',cssclass:"inner_box",style:fixed_position(100,250)+";width:auto"});
    var action=new Ajax.Request ( 'ajax_misc.php',
                                      {
                                        method:'get',
                                        parameters:{op:"display_all_card",
                                                    gDossier:p_dossier,
                                                    p_accounting:p_accounting
                                                },
                                        onSuccess:function (req)
                                        {
                                            div_dest.innerHTML=req.responseText;
                                            remove_waiting_box();
                                        }
                                      }
                                    );
}

/**
 * Search an account or an analytic account or a card, used in REPORT
 * @param {json} p_obj , 
 *          property : - op for ajax_misc  , 
 *                     - gDossier, 
 *                     - target DOM element to update with the result
 *                     - query  for the search
 * @returns {void}
 */
function search_account_card(p_obj)
{
    p_obj['op']=p_obj['op']||"search_account_card";
    var query=p_obj;
    if (p_obj.tagName && p_obj.tagName=='FORM') {
        query=p_obj.serialize(true);
    }
    
    waiting_box();
    new Ajax.Request("ajax_misc.php",{method:"get",parameters:query,
        onSuccess: function (req){
            
            var pos=calcy(50);
            var obj={id:"search_account_div",cssclass:"inner_box",style:"top:"+pos+"px",
            html:req.responseText};
            add_div(obj);
            remove_waiting_box();
        }
    });
    return false;
}
