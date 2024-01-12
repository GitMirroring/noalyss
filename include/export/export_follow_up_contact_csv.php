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

$http=new HttpInput();
$ag_id=$http->get("ag_id", "number");

if ( $g_user->can_read_action($ag_id) == false ) return;


/**
 * @file
 * @brief export a follow up in CSV 
 */
$csv=new Noalyss_CSV("followup".$ag_id);
$csv->send_header();

$document_type=$cn->get_value("select ag_type from action_gestion where ag_id=$1",[$ag_id]);

$aRow=$cn->get_array("select a.f_id,ap_id,ag_id,f.ad_value,f.ad_id,fd.fd_id,jfa.jnt_order
from action_person a 
join fiche f1 on (a.f_id=f1.f_id)
join fiche_detail f on (a.f_id=f.f_id)
join fiche_def fd on (fd.fd_id=f1.fd_id)
join jnt_fic_attr jfa on (fd.fd_id=jfa.fd_id and jfa.ad_id=f.ad_id)
where ag_id=$1 
order by f1.fd_id, a.f_id,jnt_order",[$ag_id]);

$nb=count($aRow);
$lastcat=0;$lastcard=0;
for ($i=0;$i< $nb;$i++)
{

    // for each card we sent all info
    if ($lastcard != $aRow[$i]['f_id']) {
       // Write contact option
       $aOption=$cn->get_array(" select ap_value
from 
action_person a
join action_person_option apo on (a.ap_id=apo.action_person_id)
join contact_option_ref cor on (cor.cor_id=apo.contact_option_ref_id)
where ag_id=$1 and f_id=$2  order by cor_id",array($ag_id,$lastcard));
       $nb_option=count($aOption);
       for ($h=0;$h < $nb_option;$h++) {
           $csv->add($aOption[$h]['ap_value']);
       }
       $csv->write();
       $lastcard=$aRow[$i]['f_id'];
    }
    // For each category we send the column header
    if (  $lastcat != $aRow[$i]['fd_id']) {
        // Get attribut fd
        $aColHeader = $cn->get_array("select jnt_order,
                                ad_text,
                                ad_type 
                        from fiche_def  f
                        join jnt_fic_attr jfa  using (fd_id) 
                        join attr_def ad using (ad_id)
                        where 
                            f.fd_id=$1 
                        order by jnt_order",[$aRow[$i]['fd_id']]);
        $aCol=array();
        $nb_col=count($aColHeader);
        for ( $j = 0;$j < $nb_col;$j++) {
            $aCol[]=$aColHeader[$j]['ad_text'];
        }
        
        // Add contact header option
        $aOptionCat=$cn->get_array("select
	cor.cor_label
        from
                public.jnt_document_option_contact jdoc
        join contact_option_ref cor on
                (jdoc.contact_option_ref_id = cor_id )
        where
                document_type_id = $1
                and jdoc_enable = 1 order by cor_id",array($document_type));
        $nb_col=count($aOptionCat);
        for ( $j = 0;$j < $nb_col;$j++) {
            $aCol[]=$aOptionCat[$j]['cor_label'];
        }
        $csv->write_header($aCol);
        $lastcat=$aRow[$i]['fd_id'];
    }
    $csv->add($aRow[$i]['ad_value']);
    
}
$aOption=$cn->get_array(" select ap_value
from 
action_person a
join action_person_option apo on (a.ap_id=apo.action_person_id)
join contact_option_ref cor on (cor.cor_id=apo.contact_option_ref_id)
where ag_id=$1 and f_id=$2  order by cor_id",array($ag_id,$lastcard));
$nb_option=count($aOption);
for ($h=0;$h < $nb_option;$h++) {
    $csv->add($aOption[$h]['ap_value']);
}
$csv->write();