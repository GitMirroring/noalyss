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
 * \brief module to manage the card (removing, listing, creating, modify attribut)
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
require_once NOALYSS_INCLUDE.'/lib/ac_common.php';
require_once NOALYSS_INCLUDE.'/lib/user_menu.php';
global $http;

$retour=HtmlInput::button_anchor("Retour à la liste", HtmlInput::get_to_string(array("gDossier","ac")));
$action=$http->post('action',"string", '');
/*******************************************************************************************/
// Try to remove a category
/*******************************************************************************************/
if ( $action == 'remove_cat' ) 
{
    $post_id=$http->request("fd_id","number");
    if ($post_id == 0 || $post_id >= 500000)
    {
        alert(_('Impossible d\'enlever cette catégorie'));
    } else {
        $fd_id=new Fiche_Def($cn,$post_id);
        $remains=$fd_id->remove();
        if ( $remains != 0 ) {
            /* some card are not removed because it is used */
            alert(_('Impossible d\'enlever cette catégorie, certaines fiches sont encore utilisées'."\n".
                  'Les fiches non utilisées ont cependant été effacées'));
        }
    }
    $fiche_def=new Fiche_def($cn);
    $fiche_def->display();
    return;
}
/*******************************************************************************************/
// Change some basis info
/*******************************************************************************************/
if ( isset ($_POST['change_name']))
{
	$fiche_def=new Fiche_Def($cn,$http->request('fd_id','number'));

    $label=$http->request("nom_mod");
    $fiche_def->SaveLabel($label);
    if ( isset($_REQUEST['create']))
    {
        $fiche_def->set_autocreate(true);
    }
    else
    {
        $fiche_def->set_autocreate(false);
    }
    $fiche_def->save_class_base($http->request('class_base'));
    $fiche_def->save_description($http->request('fd_description'));

	echo $fiche_def->input_detail();
	echo $retour;
	return;
}

/*******************************************************************************************/
// Save a new category of card
/*******************************************************************************************/
if ( isset($_POST['add_modele']))
{
	$single=new Single_Record("dup");
	if ($single->get_count()==0)
	{
		$single->save();
		$fiche_def=new Fiche_Def($cn);
                /**
                 * Check if we have all needed information
                 */
		if ( $fiche_def->Add($_POST) == 0 )
		{
			echo $fiche_def->input_detail();
			echo $retour;
			return;
		}
		else
		{
			$fiche_def->input_new();
			echo $retour;
			return;
		}
	}
	else
	{
		alert(_('Doublon'));
	}
}
$fiche_def_id=$http->request("fd","number",0);
$fiche_def=new Fiche_def($cn);
if ( $fiche_def_id != 0 ){
    $fiche_def->id=$fiche_def_id;
    $fiche_def->load();
    echo $fiche_def->input_detail();
} elseif ($fiche_def_id == 0)
{
    $fiche_def->display();

}
?>
