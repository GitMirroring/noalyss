<?php

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

// Copyright 2014 Author Dany De Bontridder danydb@aevalys.eu
// require_once '.php';
/**
 *@file
 *@brief insert concerned operation , call from follow up
 */

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

require_once NOALYSS_INCLUDE.'/lib/http_input.class.php';
require_once NOALYSS_INCLUDE.'/class/card_multiple.class.php';
$http=new HttpInput();

ob_start();
try
{
    $ag_id=$http->get("ag_id");
}
catch (Exception $exc)
{
    echo $exc->getMessage();
    error_log($exc->getTraceAsString());
    return;
}
/*
 * security Who can do it ?
 */
if ( ! $g_user->can_write_action($ag_id)  ) {
    record_log(__FILE__."security : access refused");
    return;
}

require_once NOALYSS_INCLUDE.'/class/acc_ledger.class.php';

$r=HtmlInput::title_box(_("Détail fiche"), 'search_card');
//--------------------------------------------------------------------------------------------------------------------
// Form search
//--------------------------------------------------------------------------------------------------------------------
$r.='<form id="search_card1_frm" method="GET" onsubmit="action_concerned_search_card(this);return false;">';
$q=new IText('query');
$q->value=(isset($query))?$query:'';
$r.='<span style="margin-left:50px">';
$r.=_('Fiche contenant').Icon_Action::infobulle(19);
$r.=$q->input();
// where to search
$select=new ISelect("search_in");
$select->value=$cn->make_array("select ad_id, ad_text from attr_def order by 2 ",1);
$select->selected=$http->get("search_in","string","");
$r.=_("limité aux champs ").$select->input();
$r.=HtmlInput::submit('fs', _('Recherche'), "", "smallbutton");
$r.='</span>';
$r.=dossier::hidden().HtmlInput::hidden('op2', 'add_concerned_card');
$r.=HtmlInput::request_to_hidden(array('ag_id'));
$r.='</form>';


$query=$http->get("query", "string","");
$sql_array['query']=$query;
$sql_array['typecard']='all';

$fiche=new Card_Multiple($cn);
/* Build the SQL and show result */
$sql=$fiche->build_sql($sql_array);
$count_card=$fiche->count_sql($sql_array);

//--------------------------------------------------------------------------------------------------------------------
// Result
//--------------------------------------------------------------------------------------------------------------------

/* We limit the search to MAX_SEARCH_CARD records */
$a=$cn->get_array($sql);
$r.='<form id="search_card2_frm" method="POST" onsubmit="action_concerned_save_card(this);return false;" '
        . ' style="height:45rem">';
$r.=HtmlInput::request_to_hidden(array('ag_id'));
$r.=dossier::hidden().HtmlInput::hidden('op2', 'link_concerned_card').HtmlInput::hidden("op","card");
// element to update with the answer
$r.=HtmlInput::hidden("ctl","concerned_card_td");
for ($i=0; $i<count($a); $i++)
{
    $ic=new ICheckBox("selected_card[]");
    $ic->value=$a[$i]['f_id'];
    $array[$i]['checkbox']=$ic->input();
    $array[$i]['quick_code']=$a[$i]['quick_code'];
    $array[$i]['name']=h($a[$i]['vw_name']);
    $array[$i]['accounting']=$a[$i]['accounting'];
    $array[$i]['first_name']=h($a[$i]['vw_first_name']);
    $array[$i]['description']=h($a[$i]['vw_description']);
}//foreach

// No accountancy history
$accvis=0; 
echo $r;
require_once(NOALYSS_TEMPLATE.'/card_multiple_result.php');
echo '<ul class="aligned-block">';
echo '<li>';
echo HtmlInput::submit("add_concerned_card",_("Ajouter"));
echo '</li>';
echo '<li>';
echo HtmlInput::button_close("search_card");
echo '</li>';
echo '</ul>';
echo '</form>';


$response=ob_get_contents();
ob_end_clean();


$html=escape_xml($response);
if ( !headers_sent() ) { header('Content-type: text/xml; charset=UTF-8');} else {echo $response;echo $html;}
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl>unused</ctl>
<code>$html</code>
</data>
EOF;
?>