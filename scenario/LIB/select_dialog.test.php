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

/**
 * @file
 * @brief  test the select_dialog class
 * @see Select_Dialog
 */
require_once NOALYSS_INCLUDE."/lib/select_dialog.class.php";

$selbox=new Select_Dialog("action_add_action2", _("Ajout action"));
$aDocumentType=$cn->get_array("select dt_id,dt_value from document_type order by 2");
$nbDocumentType=count($aDocumentType);
$dossier_id=Dossier::id();
for ($i=0; $i<$nbDocumentType; $i++)
{
    $selbox->add_url($aDocumentType[$i]['dt_value'],
            "do.php?".http_build_query([
                "gDossier"=>$dossier_id,
                "sa"=>"update",
                "add_action_here"=>"yes",
                "action_type"=>$aDocumentType[$i]["dt_id"]]));
}
echo $selbox->input();
