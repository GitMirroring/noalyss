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

// Copyright Author Dany De Bontridder danydb@aevalys.eu

/*!\file
 * \brief Fid for the ajax request for cards
 * \see fiche_search.php
 * Valid parameter GET are
 * - d type = cred, deb, all or filter or any sql where clause if the d starts with [sql]
 * - j is the legdger
 * - l field for the label
 * - t field for the tva_id
 * - p field for the price (sale)
 * - b field for the price (purchase)
 * - FID is the QuickCode
   *\note if the j is -1 then all the card are shown
 */
require_once '../include/constant.php';
require_once NOALYSS_INCLUDE.'/class/noalyss_parameter_folder.class.php';
require_once NOALYSS_INCLUDE.'/class/database.class.php';
require_once NOALYSS_INCLUDE.'/lib/user_common.php';
require_once NOALYSS_INCLUDE.'/lib/http_input.class.php';
require_once NOALYSS_INCLUDE.'/class/dossier.class.php';
/**
 * if not connected, session is expired then exit with a message NOCONX
 */
if ( ! isset($_SESSION[SESSION_KEY."g_user"])) {
    echo "NOCONX";
    die();
}

$gDossier=dossier::id();

require_once('class/user.class.php');

$cn=Dossier::connect();
global $g_user;
$g_user=new User($cn);
$g_user->check();
$g_user->check_dossier(dossier::id());
set_language();

$hi=new HttpInput();

$fLabel=$hi->request("l","string","none");
$fTva_id=$hi->request("t","string","none");
$fPrice_sale=$hi->request("p","string","none");
$fPrice_purchase=$hi->request("b","string","none");

if ( isset($_SESSION[SESSION_KEY.'isValid']) && $_SESSION[SESSION_KEY.'isValid'] == 1)
{
    $jrn=$hi->get('j', "number",'-1');
    $d=$hi->get('d',"string", '');
    $d=sql_string($d);

    if ( $jrn == -1 )
        $d='all';
    if ( strpos($d,'sql') == false )
    {

        switch ($d)
        {
        case 'cred':
            $filter_jrn=$cn->make_list("select jrn_def_fiche_cred from jrn_def where jrn_def_id=$1",array($jrn));
            $filter_card=($filter_jrn != "")?" and fd_id in ($filter_jrn)":' and false ';

            break;
        case 'deb':
            $filter_jrn=$cn->make_list("select jrn_def_fiche_deb from jrn_def where jrn_def_id=$1",array($jrn));
            $filter_card=($filter_jrn != "")?" and fd_id in ($filter_jrn)":' and false ';
            break;
        case 'all':
            $filter_card="";
            break;
        case 'filter':
			 $get_cred='jrn_def_fiche_cred';
        $get_deb='jrn_def_fiche_deb';
		$deb=$cn->get_value("select $get_deb from jrn_def where jrn_def_id=$1",array($jrn));
		$cred=$cn->get_value("select $get_cred from jrn_def where jrn_def_id=$1",array($jrn));

		$filter_jrn="";

		if ($deb!=='' && $cred!='')
			$filter_jrn	=$deb.','.$cred;
		elseif($deb != '')
			$filter_jrn=$deb;
		elseif($cred != '')
			$filter_jrn=$cred;

		$filter_card=($filter_jrn != "")?" and fd_id in ($filter_jrn)":' and false ';
            break;
        case 'all':
            $filter_card='';
            break;

        default:
            $filter_card="and fd_id in ($d)";
        }
    }
    else
    {
        $filter_card=$d;
        $filter_card=str_replace('[sql]','',$d);
    }
    $sql="select vw_name,vw_addr,vw_cp,vw_buy,vw_sell,tva_id
         from vw_fiche_attr
         where quick_code=upper($1)". $filter_card;

    $array=$cn->get_array($sql,  array($hi->request('FID')));

    if ( empty($array))
    {
        echo '{"answer":"nok","flabel":"'.$fLabel.'"}';
        exit;
    }


    $name=$array[0]['vw_name'];
    $sell=(isNumber($array[0]['vw_sell']) == 1) ? $array[0]['vw_sell'] : 0 ;
    $buy=(isNumber($array[0]['vw_buy']) == 1) ?$array[0]['vw_buy']:0;
    
    $tva_id=$array[0]['tva_id'];

    // Check null
    $name=($name==null)?" ":str_replace('"','',$name);
    $sell=($sell==null)?"0":str_replace('"','',$sell);
    $buy=($buy==null)?"0":str_replace('"','',$buy);
    $tva_id=($tva_id==null)?" ":str_replace('"','',$tva_id);
    /* store the answer in an array and transform it later into a JSON object */
    $tmp=array();
    $tmp[]=array('flabel',$fLabel);
    $tmp[]=array('name',$name);
    $tmp[]=array('ftva_id',$fTva_id);
    $tmp[]=array('tva_id',$tva_id);
    $tmp[]=array('fPrice_sale',$fPrice_sale);
    $tmp[]=array('sell',$sell);
    $tmp[]=array('fPrice_purchase',$fPrice_purchase);
    $tmp[]=array('buy',$buy);
    $a='{"answer":"ok"';
    for ($o=0;$o < count($tmp);$o++)
    {
        $a.=sprintf(',"%s":"%s"',$tmp[$o][0],$tmp[$o][1]);
    }
    $a.='}';
}
else
    $a='{"answer":"unauthorized"}';
header("Content-type: text/html; charset: utf8",true);
print $a;
?>
