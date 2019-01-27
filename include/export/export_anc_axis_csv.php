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
/**
 * @file
 * Export ANALYTIC Axis in CSV
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');

require_once NOALYSS_INCLUDE.'/lib/noalyss_csv.class.php';
$http=new HttpInput();
$pa_id=$http->get("pa_id","number");
$name=$cn->get_value("select pa_name from plan_analytique where pa_id=$1",
    [$pa_id]);
$array=$cn->get_array(" select
        po_name,
        po_amount,
        po_description,
        ga_description 
      from 
         poste_analytique 
        left join groupe_analytique using (ga_id)
  where poste_analytique.pa_id=$1 order by po_name asc ",[$pa_id]);
$output=new Noalyss_Csv($name);
$output->send_header();
$output->write_header([
    _("Nom"),
    _("Description"),
    _("Montant"),
    _("Groupe"),
]);
if ($array != FALSE )
{
    $nb_array=count($array);
    for ($i=0;$i<$nb_array;$i++) {
        $output->add($array[$i]['po_name']);
        $output->add($array[$i]['po_description']);
        $output->add($array[$i]['po_amount'],"number");
        $output->add($array[$i]['ga_description']);
        $output->write();
    }

}