<?php

/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2002-2020) Author Dany De Bontridder <danydb@noalyss.eu>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');
require_once NOALYSS_INCLUDE.'/class/follow_up.class.php';
require_once NOALYSS_INCLUDE.'/lib/noalyss_csv.class.php';

$http=new HttpInput();
$ag_id=$http->get("ag_id", "number");

if ( $g_user->can_read_action($ag_id) == false ) return;


/**
 * @file
 * @brief export a follow up in CSV 
 */
$csv=new Noalyss_CSV("followup".$ag_id);
$csv->send_header();

$document=new Follow_Up($cn);
echo $document->export_csv(["tdoc"=>$ag_id]);

$ret=$cn->exec_sql("select f_id,(select ad_value from fiche_detail where f_id=a.f_id and ad_id=25) as company,
(select ad_value from fiche_detail where f_id=a.f_id and ad_id=1) as name,
(select ad_value from fiche_detail where f_id=a.f_id and ad_id=32) as first_name,
(select ad_value from fiche_detail where f_id=a.f_id and ad_id=23) as quick_code,
cor.cor_label,
apo.ap_value 
from action_person a 
left join action_person_option apo ON  (a.ap_id=apo.action_person_id)
left join contact_option_ref cor on (apo.contact_option_ref_id=cor.cor_id)
where ag_id=$1",[$ag_id]);
$aTitle= array ( 
            array("title"=>_("fiche_id"), "type"=>"string"),
            array("title"=>_("société"), "type"=>"string"),
            array("title"=>_("nom"), "type"=>"string"),
            array("title"=>_("prénom"), "type"=>"string"),
            array("title"=>_("qcode"), "type"=>"string"),
            array("title"=>_("option"), "type"=>"string"),
            array("title"=>_("valeur"), "type"=>"string"));
$cn->query_to_csv($ret,$aTitle);

$ret=$cn->exec_sql('select t_tag,t_description from action_tags join tags using (t_id) where ag_id=$1',[$ag_id]);
$aTitle=array(
    array('title'=>_('Etiquette'),"type"=>"string"),
    array('title'=>_("Description"),"type"=>"string")
);
$cn->query_to_csv($ret,$aTitle);