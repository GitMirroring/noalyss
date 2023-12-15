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
/*! \file
 * \brief Return the balance in CSV format
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');

include_once ("lib/ac_common.php");
include_once("class/acc_balance.class.php");

$http=new HttpInput();
$gDossier=dossier::id();

$cn=Dossier::connect();
bcscale(2);

$http=new HttpInput();
$export=new Noalyss_Csv('balance');
$bal=new Acc_Balance($cn);
$bal->jrn=null;
$p_filter=$http->get("p_filter");
switch( $p_filter)
{
case 0:
        $bal->jrn=null;
    break;
case 1:
    if (  isset($_GET['r_jrn']))
    {
        $selected=$http->get('r_jrn');
        $array_ledger=$g_user->get_ledger('ALL',3);
        $array=get_array_column($array_ledger,'jrn_def_id');
        for ($e=0;$e<count($selected);$e++)
        {
            if (isset ($selected[$e]) && in_array ($selected[$e],$array) )
                $bal->jrn[]=$selected[$e];
        }
    }
    break;
case 2:
    if ( isset($_GET['r_cat']))   $bal->filter_cat($http->get('r_cat'));
    break;
}

$bal->from_poste=$http->get('from_poste');
$bal->to_poste=$http->get('to_poste');

if (isset($_GET['unsold'])) $bal->unsold=true;
$prev = (isset($_GET['previous_exc'])) ? 1: 0;

$row=$bal->get_row($http->get('from_periode','number'),
                   $http->get('to_periode','number'),
        $prev);
$prev =  ( isset ($row[0]['sum_cred_previous'])) ?1:0;
$title=array('poste','libelle');
if ($prev  == 1 ) $title=array_merge($title,array('deb n-1','cred n-1','solde n-1','d/c;'));
$title=array_merge($title,array(_('Débit'),_('Crédit'),_("Solde débiteur"),_("Solde créditeur")));

$export->send_header();
$pstart = new Periode($cn,$http->get("from_periode"));
$pend= new Periode($cn,$http->get("to_periode"));

$export->add(sprintf (_("balance du %s au %s"),$pstart->first_day(),$pend->last_day()));
$export->write();
$export->write_header($title);

foreach ($row as $r)
{
    $export->add($r['poste']);
    $export->add($r['label']);
    
    if ( $prev == 1 )
    {
        $delta=bcsub($r['solde_deb_previous'],$r['solde_cred_previous']);
        $sign=($delta<0)?'C':'D';
        $sign=($delta == 0)?'=':$sign;
        $export->add($r['sum_deb_previous'],"number");
        $export->add($r['sum_cred_previous'],"number");
        $export->add(abs($delta),"number");
        $export->add($sign);
       
    }
    $delta=bcsub($r['solde_deb'],$r['solde_cred']);
    $export->add($r['sum_deb'],"number");
    $export->add($r['sum_cred'],"number");

    if ( $delta < 0){
        $export->add("0","number");
        $export->add($r['solde_cred'],"number");
    }elseif ( $delta > 0 ) {
        $export->add($r['solde_deb'],"number");
        $export->add("0","number");
    }elseif ($delta==0 && $r['poste']!="")
    {
        $export->add("0","number");
        $export->add("0","number");
    }elseif ( $delta == 0 && $r['poste'] =="" ) {
        $export->add($r['solde_deb'],"number");
        $export->add($r['solde_cred'],"number");
    } else {
        throw new \Exception(__FILE__.":".__LINE__);
    }
    $export->write();
}


?>
