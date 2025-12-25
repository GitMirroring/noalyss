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
 * @brief export in CSV the export of history
 *
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
$export=new Noalyss_Csv(_('historique-stock'));
$stock=new Stock($cn);
$sql = $stock->create_query_histo($_GET);
$sql .= " order by  real_date asc";

$res=$cn->exec_sql($sql);
$max_row=Database::num_row($res);
$export->send_header();

$export->write_header(array(_("Date"),
                            _("N° pièce"),
                            _('Code Stock'),
                            _('Depot'),
                            _('Code Stock'),
                            _('Nom Stock'),
                            _("Code Tiers"),
                            _("Nom Tiers"),
                            _('Commentaire'),
                            _('Quantité'),
                            _('Prix unitaire'),
                            _('IN/OUT')));
		
for ($i=0;$i<$max_row;$i++)
{
	$row=Database::fetch_array($res,$i);
	$export->add($row['cdate']);
	$export->add($row['jr_pj_number']);
	$export->add($row['sg_code']);
	$export->add($row['r_name']);
	$export->add($row['qcode']);
	$export->add($row['fname']);
	$export->add($row['qcode_tiers']);
	$export->add($row['fname_tiers']);
	$row['ccomment']=noalyss_str_replace('"','',$row['ccomment']);
	$export->add($row['ccomment']);
	$export->add($row['sg_quantity'],"number");
	$export->add($row['j_montant'],"number");
	$export->add($row['direction']);
	$export->write();

}

?>