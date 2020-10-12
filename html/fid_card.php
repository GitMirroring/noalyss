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
 * \brief this file is used by the autocomplete functionnality
 *\see ICard
 */

require_once '../include/constant.php';
require_once NOALYSS_INCLUDE.'/class/database.class.php';
require_once NOALYSS_INCLUDE.'/lib/http_input.class.php';
require_once NOALYSS_INCLUDE.'/class/dossier.class.php';
/*!\brief
 *  Received parameters are
 *   - j for the ledger
 *   - e for extra (typecard)
 *   - type is the ledger type (ach, ven, fin, gl or nothing)
 *   - FID contains the string the user is typing
 *\note the typecard can be
 *   - cred card for the debit only if j is set
 *   - deb card for the debit only if j is set
 *   - filter card for debit and credit only if j OR type is set
 *   - list of fd_id
 *
 */
$http=new HttpInput();

$jrn= $http->request("j","number",-1); 
$limit=$http->request("limit","number",12);

$filter_card="";
$cn=Dossier::connect();
$d=$http->request('e');
$filter_card='';

require_once('class/user.class.php');
global $g_user;
$g_user=new User($cn);
$g_user->check();
$g_user->check_dossier(dossier::id());
set_language();

if ( $d == 'all')
{
    $filter_card='';
}
else if (strpos($d,'sql]')==true)
{
	$filter_card=  str_replace('[sql]', " and ", $d);
} else
    $filter_card="and fd_id in ($d)";

if ( $jrn != -1 )
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

    }
}
else
{
    if (isset($_REQUEST['type']))
    {
        if ($_REQUEST['type']=='gl' || $_REQUEST['type']=='') $filter_card='';
        else
        {
            $get_cred='jrn_def_fiche_cred';
            $get_deb='jrn_def_fiche_deb';
            $type=$http->request("type");
            $filter_jrn=$cn->make_list("select $get_cred||','||$get_deb as fiche from jrn_def where jrn_def_type=$1",array($type));
            $filter_card=($filter_jrn != "")?" and fd_id in ($filter_jrn)":' and false ';

        }
    }
}
// filter only enable card
$filter_enable_card=" and f_id in (select f_id from fiche_detail where ad_value = '1' and ad_id=54) ";

$sql_str="select distinct f_id 
         from fiche 
         join fiche_detail using (f_id) 
         where 
         ad_id in (9,1,23) 
         and ad_value ilike '%'||$1||'%' ".$filter_enable_card.$filter_card.' limit '.$limit;


$fid=$http->request("FID");

$sql=$cn->get_array($sql_str		    ,array($fid));

if ($sql != false && sizeof($sql) != 0 )
{
    echo "<ul>";
    $sql_get=$cn->prepare('get_name',"select ad_value from fiche_detail where f_id = $1 and ad_id=$2");

    for ($i =0;$i<12 && $i < count($sql) ;$i++)
    {
        $name='';
        $quick_code='';
        $desc='';

        $sql_name=$cn->execute('get_name',array($sql[$i]['f_id'],1));
        if ( Database::num_row($sql_name) == 1) $name=Database::fetch_result($sql_name,0,0);

        $sql_name=$cn->execute('get_name',array($sql[$i]['f_id'],9));
        if ( Database::num_row($sql_name) == 1) $desc=Database::fetch_result($sql_name,0,0);

        $sql_name=$cn->execute('get_name',array($sql[$i]['f_id'],23));
        if (Database::num_row($sql_name) == 1) $quick_code=Database::fetch_result($sql_name,0,0);
	$fid=htmlentities($fid);

        /* Highlight the found pattern with bold format */
        $name=str_ireplace($fid,'<em>'.$fid.'</em>',h($name));
        $qcode=str_ireplace($fid,'<em>'.$fid.'</em>',h($quick_code));
        $desc=str_ireplace($fid,'<em>'.$fid.'</em>',h($desc));
        printf('<li id="%s">%s <span class="informal">%s %s</span></li>',
               $quick_code,
               $quick_code,
               $name,
               $desc
              );
    }
    echo '</ul>';
    if (count($sql) > 12)
    {
        printf ('<i>...'._('Résultat limité à 12').'  ...</i>');
    }
}
else
{
    echo "<ul><li>"._("Non trouvé")."</li></ul>";
}
?>
