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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 7/08/24
/*! 
 * \file
 * \brief ajax from CCARD : add , remove or sort attributes from CCARD
 */
if (!defined('ALLOWED')) die('Appel direct ne sont pas permis');
global $g_user;

if ($g_user->check_module('CCARD') == 0) return;
$http = new HttpInput();
try {
    $fiche_def = $http->request('fiche_def_id', 'number');
    $op2 = $http->request("op2");
} catch (\Exception $e) {
    echo $e->getMessage();
    return;
}

global $cn;

if ($op2 == 'add') {
    $ad_id=$http->get("ad_id","number");
    $row = $cn->get_row('select ad_id , ad_text from attr_def where ad_id=$1',[$ad_id]);
    Fiche_Def::print_existing_attribut($row['ad_id'], $row['ad_text']);

} elseif ($op2 == 'remove') {
    $ad_id=$http->get("ad_id","number");
    $row = $cn->get_row('select ad_id , ad_text from attr_def where ad_id=$1',[$ad_id]);
    Fiche_Def::print_available_attribut($row['ad_id'], $row['ad_text'],'');
} elseif ($op2 == 'save') {

    $str_Attribut=$http->post("attribut");
    parse_str($str_Attribut, $row);
    $aAttribut=$row['attribut_card'];
    $nb_attribut=count($aAttribut);
    try {
        $cn->start();
        $cn->exec_sql("create temporary table xxattribut (x_order int, x_adid int, x_fdid int)");
        $order = 10;
        for( $i = 0;$i < $nb_attribut;$i++) {
            $o=$order*($i+1);
            $cn->exec_sql("insert into xxattribut values ($1,$2,$3)",[$o,$aAttribut[$i],$fiche_def]);
        }
        $cn->exec_sql("delete from jnt_fic_attr where fd_id=$1 and ad_id not in (select x_adid from xxattribut)",[$fiche_def]);
        $cn->exec_sql("insert into jnt_fic_attr(ad_id,fd_id,jnt_order) 
                                select x_adid,x_fdid,x_order 
                                from xxattribut 
                                on conflict do nothing ");
        $cn->exec_sql('update jnt_fic_attr set jnt_order = x_order from xxattribut where ad_id=x_adid and fd_id=x_fdid');
        $cn->commit();
        echo 'OK';
    } catch (\Exception $e) {
        echo $e->getMessage();
        $cn->rollback();
    }

} else {
    die("[$op2] action inconnue");
}