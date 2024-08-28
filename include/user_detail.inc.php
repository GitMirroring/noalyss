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
/** \file
 * \brief Users Security
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
require_once NOALYSS_INCLUDE.'/lib/ac_common.php';
require_once NOALYSS_INCLUDE.'/lib/user_menu.php';
$http=new HttpInput();
$rep = new Database();
try {
$uid = $http->request('use_id');
} catch (Exception $ex) {
     echo_error($ex->getMessage());
     throw $ex;
}
$UserChange = new Noalyss_user($rep, $uid);

if ($UserChange->id == false)
{
    // Message d'erreur
    html_page_stop();
}


$UserChange->load();
$it_pass=new IText('password');
$it_pass->javascript='onkeyup="check_password_strength(\'password\',\'password_info\',1)"';
$it_pass->value="";
?>
<FORM  id="user_detail_frm" METHOD="POST">

<?php echo HtmlInput::hidden('UID',$uid)?>
<?php echo HtmlInput::hidden('use_id',$uid)?>
    <TABLE BORDER=0>
        <TR>

<?php printf('<td>login</td><td> %s</td>', $UserChange->login); ?>
            </TD>
        </tr>
        <TR>
            <TD>
            <?php printf('Nom de famille </TD><td><INPUT class="input_text"  type="text" NAME="lname" value="%s"> ', $UserChange->name); ?>
            </TD>
        </TR>
        <TR>
          <?php printf('<td>prénom</td><td>
             <INPUT class="input_text" type="text" NAME="fname" value="%s"> ', $UserChange->first_name);
                ?>
        </TD>
        </TR>
        <tr>
            <td>
                <?php 
                echo _('email');
                ?>
            </td>
            <td>
                <INPUT class="input_text" type="text" NAME="email" value="<?php echo $UserChange->email;?>">
            </td>
        </tr>
        <tr>
            <td>
                Mot de passe :<span class="info">Laisser à VIDE pour ne PAS le changer</span>
            </td>
            <td>
                <?php echo $it_pass->input();?>
                <span id="password_info" style="background-color: rgba(255,160,122,0.58);color:orangered;position:absolute"></span>
            </td>
        </tr>
        <tr>
            <td>
                <?php echo _('Actif');?>
            </td>
            <td>
                <?php
                $select_actif=new ISelect('Actif');
                $select_actif->value=array(
                    array('value'=>0,'label'=>_('Non')),
                    array('value'=>1,'label'=>_('Oui'))
                );
                $select_actif->selected=$UserChange->active;
                echo $select_actif->input();
                ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php echo _('Type');?>
            </td>
            <td>
                <?php
                $select_admin=new ISelect('Admin');
                $select_admin->value=array(
                    array('value'=>0,'label'=>_('Utilisateur normal')),
                    array('value'=>1,'label'=>_('Administrateur'))
                );
                $select_admin->selected=$UserChange->admin;
                echo $select_admin->input();
                ?>
            </td>
        </tr>
    </table>

    <input type="hidden" name="sbaction" id="sbaction" value="save">

        <input type="Submit" class="button" NAME="SAVE" VALUE="<?=('Sauver les changements')?>" onclick="return confirm_box('user_detail_frm','<?=_('Confirmer')?>');">

        <input type="button"  class="button" NAME="DELETE" VALUE="<?=('Effacer')?>" onclick="$('delete_user_div').show();" >

</FORM>
<div id="delete_user_div" class="inner_box" style="display: none">
<?=HtmlInput::title_box(_("Effacer"),'delete_user_div','hide')?>
<FORM  id="user_detail_frm" METHOD="POST">
    <INPUT   type="hidden" NAME="lname" value="<?=_("$UserChange->name")?>">
    <INPUT type="hidden" NAME="fname" value="<?=_("$UserChange->first_name")?>">
    <?php echo HtmlInput::hidden('UID',$uid)?>
    <?php echo HtmlInput::hidden('use_id',$uid)?>
    <input type="hidden" name="sbaction" value="delete">
    <p  class="info" id="codedel_div">
        <?php
        echo _("Pour effacer , confirmez en retapant le code");
        echo confirm_with_string('userdel','5');
        ?>

    </p>
    <ul class="aligned-block">
        <li>
            <input type="Submit"  class="button" NAME="DELETE" VALUE="<?=_("Confirmer")?>">
        </li>
        <li>
            <?=HtmlInput::button_hide('delete_user_div')?>
        </li>
    </ul>
</FORM>
</div>

<?php
if  ($UserChange->admin == 0 ) :
?>
        <!-- Show all database and rights -->
        <H2 class="info"> Accès aux dossiers</H2>
        <p class="notice">
            Les autres droits doivent être réglés dans les dossiers (paramètre->sécurité), le fait de changer un utilisateur d'administrateur à utilisateur
			normal ne change pas le profil administrateur dans les dossiers.
			Il faut aller dans C0SEC pour diminuer ses privilèges.
        </p>
     
<?php
$array = array(
    array('value' => 'X', 'label' => 'Aucun Accès'),
    array('value' => 'R', 'label' => 'Utilisateur normal')
);
$repo = new Dossier(0);
if ( $repo->count() == 0) 
{
    echo hb('* Aucun Dossier *');
    echo '</div>';
    return;
}

$Dossier = $repo->show_dossier('R',$UserChange->login);

$mod_user = new Noalyss_user(new Database(), $uid);
?>
           <TABLE id="database_list" class="result">
<?php 
//
// Display all the granted folders
//
$i=0;
foreach ($Dossier as $rDossier):
    $i++;
$class=($i%2==0)?' even ':'odd ';
?>
            <tr id="row<?php echo $rDossier['dos_id']?>" class="<?php echo $class;?>">
                <td>
                    <?php echo h($rDossier['dos_name']); ?>
                </td>
                <td>
                    <?php echo h($rDossier['dos_description']); ?>
                </td>
                <td>
                    <?php echo HtmlInput::anchor(_('Enleve'),"",
                            " onclick=\"folder_remove({$mod_user->id},{$rDossier['dos_id']});\"");?>
                </td>
                
            </tr>
<?php 	
endforeach;
?>
        </TABLE>
        <?php 
               echo HtmlInput::button("database_add_button",_('Ajout'),
                            " onclick=\"folder_display({$mod_user->id});\"");
        ?>
        <?php
        // If UserChange->admin==1 it means he can access all databases
        //
        else :
        ?>
        
<?php
    endif;
?>

</DIV>

<?php
html_page_stop();
?>


