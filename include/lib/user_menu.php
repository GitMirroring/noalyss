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
 *   Foundation, Inshowc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
/*!\file
 * \brief Nearly all the menu are here, some of them returns a HTML string, others echo
 * directly the result.
 */

// Copyright Author Dany De Bontridder danydb@aevalys.eu


/*!   MenuAdmin 
 * \brief show the menu for user/database management
 * \return HTML code with the menu
 */

function MenuAdmin()
{
    $def=-1;
    $http=new HttpInput();

    if (isset($_REQUEST['UID']))
        $def=0;
    if ( isset ($_REQUEST['action']))
    {
        $action=$http->request('action');
        switch ($action)
        {
        case 'user_mgt':
            $def=0;
            break;
        case 'dossier_mgt':
            $def=1;
            break;
        case 'modele_mgt':
            $def=2;
            break;
	case 'audit_log':
	  $def=4;
	  break;
        case 'restore':
            $def=3;
            break;
        case 'upgrade':
            $def = 5;
            break;
        case 'info':
            $def=6;
            break;
        }
    }
	if (!defined("MULTI")||(defined("MULTI")&&MULTI==1))
	{
		$tmp_item=array (
                 array("admin-noalyss.php?action=user_mgt",_("Utilisateurs"),_('Gestion des utilisateurs'),0),
                 array("admin-noalyss.php?action=dossier_mgt",_("Dossiers"),_('Gestion des dossiers'),1),
                 array("admin-noalyss.php?action=modele_mgt",_("Modèles"),_('Gestion des modèles'),2),
                 array("admin-noalyss.php?action=restore",_("Restaure"),_("Restaure une base de données"),3),
                 array("admin-noalyss.php?action=upgrade",_("Installation"),
                     _("Installation Mise à jour du système et des bases de données"),5),
                 array("admin-noalyss.php?action=audit_log",_("Audit"),_("Utilisateurs qui se sont connectés"),4),
                 array("admin-noalyss.php?action=info",
                     _("Information système"),('Information à propos de votre installation'),6),
                 array("login.php",_("Accueil"),"",7),
                 array("logout.php",_("Sortie"),"",8)
                );
                if ( SYSINFO_DISPLAY == false ) {
                    $nb_item = count($tmp_item);
                    for ($i=0;$i<$nb_item;$i++) {
                        if ($tmp_item[$i][3] <> 6 ) {
                            $item[]=$tmp_item[$i];
                        }
                    }
                } else {
                    $item = $tmp_item;
                }
	}
	else
	{
		$item=array (array("admin-noalyss.php?action=user_mgt",_("Utilisateurs"),_('Gestion des utilisateurs'),0),
                 array("admin-noalyss.php?action=audit_log",_("Audit"),_("Utilisateurs qui se sont connectés"),4),
                 array("admin-noalyss.php?action=info",
                     _("Information système"),('Information à propos de votre installation'),6),
                 array("login.php",_("Accueil")),
                 array("logout.php",_("Sortie"))
                );

	}
    $menu=ShowItem($item,'H',"nav-item","nav-link",$def,'nav nav-pills nav-fill ');
    return $menu;
}

/*!
 * \brief  Show the menu from the pcmn page
 *
 * \param $p_start class start default=1
 *
 *
 *
 * \return nothing
 *
 *
 */

function menu_acc_plan($p_start=1)
{
    $http=new HttpInput();
    $base="?ac=".$http->request('ac');
    $str_dossier="&".dossier::get();
    for ($i=0;$i<10;$i++) { $class[$i]="tabs";}
    $class[$p_start]="tabs_selected";
    $idx=0;
    ?>
    <ul class="tabs">
    <li class="<?php echo $class[$idx];$idx++; ?>"><A HREF="<?php echo $base.'&p_start=0'.$str_dossier; ?>">0 <?php echo _(' Hors Bilan')?></A></li>
    <li class="<?php echo $class[$idx];$idx++; ?>"><A HREF="<?php echo $base.'&p_start=1'.$str_dossier; ?>">1 <?php echo _(' Immobilisé')?></A></li>
    <li class="<?php echo $class[$idx];$idx++; ?>"><A HREF="<?php echo $base.'&p_start=2'.$str_dossier; ?>">2 <?php echo _('Actif a un an au plus')?></A></li>
    <li class="<?php echo $class[$idx];$idx++; ?>"><A HREF="<?php echo $base.'&p_start=3'.$str_dossier; ?>">3 <?php echo _('Stock et commande')?></A></li>
    <li class="<?php echo $class[$idx];$idx++; ?>"><A HREF="<?php echo $base.'&p_start=4'.$str_dossier; ?>">4 <?php echo _('Compte tiers')?></A></li>
    <li class="<?php echo $class[$idx];$idx++; ?>"><A HREF="<?php echo $base.'&p_start=5'.$str_dossier; ?>">5 <?php echo _('Financier')?></A></li>
    <li class="<?php echo $class[$idx];$idx++; ?>"><A HREF="<?php echo $base.'&p_start=6'.$str_dossier; ?>">6 <?php echo _('Charges')?></A></li>
    <li class="<?php echo $class[$idx];$idx++; ?>"><A HREF="<?php echo $base.'&p_start=7'.$str_dossier; ?>">7 <?php echo _('Produits')?></A></li>
    <li class="<?php echo $class[$idx];$idx++; ?>"><A HREF="<?php echo $base.'&p_start=8'.$str_dossier; ?>">8 <?php echo _('Hors Comptabilité')?></A></li>
    <li class="<?php echo $class[$idx];$idx++; ?>"><A HREF="<?php echo $base.'&p_start=9'.$str_dossier; ?>">9 <?php echo _('Hors Comptabilité')?></A></li>
    </ul>
<div style="clear: both"></div>
<?php
}
?>
