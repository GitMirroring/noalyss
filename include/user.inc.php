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
if ( !defined ('ALLOWED')) die('Forbidden');
/*!\file
 *
 *
 * \brief user managemnt, included from admin-noalyss,
 * action=user_mgt
 *
 */
$http=new HttpInput();
echo '<div class="content" >';
\Noalyss\Dbg::echo_file(__FILE__);
/******************************************************/
// Add user
/******************************************************/
if ( isset ($_POST["ADD"]) )
{
    $cn=new Database();
    $pass5=md5($_POST['PASS']);
    $new_user=new User($cn,0);
    $new_user->first_name=$http->post('FNAME');
    $new_user->last_name=$http->post('LNAME');
    $login=$http->post('LOGIN');
    $login=str_replace("'","",$login);
    $login=str_replace('"',"",$login);
    $login=str_replace(" ","",$login);
    $login=strtolower($login);
    $new_user->login=$login;
    $new_user->setPassword($pass5);
    $new_user->email=$http->post('EMAIL',"string",'');
    if ( trim($login)=="")
    {
            alert(_("Le login ne peut pas être vide"));
    }
    else
    {
        $exist_user=$cn->get_value("select count(*) from ac_users where use_login=lower($1)",[$login]);
        if ( $exist_user == 0 ) {
            $new_user->insert();
            $new_user->load();
             put_global(array(['key'=>'use_id',"value"=>$new_user->id]));
            User::audit_admin(sprintf('ADD USER %s %s',$new_user->id,$login));
        } else {
     echo_warning(_("Utilisateur existant"));
            $uid=$cn->get_value("select use_id from ac_users where use_login=lower($1)",[$login]);
            $new_user->setId($uid);
            put_global(array(['key'=>'use_id',"value"=>$new_user->id]));
            $new_user->load();
        }

        require_once NOALYSS_INCLUDE.'/user_detail.inc.php';
        return;

    }
} //SET login
/******************************************************/
// Update  user
/******************************************************/
$sbaction=$http->post('sbaction',"string", "");
if ($sbaction == "save")
{
    $uid = $http->post("UID");

    // Update User
    $cn = new Database();
    $UserChange = new User($cn, $uid);
    
    if ($UserChange->load() == -1)
    {
        alert(_("Cet utilisateur n'existe pas"));
    }
    else
    {
        $UserChange->first_name =$http->post('fname');
        $UserChange->last_name = $http->post('lname');
        $UserChange->active = $http->post('Actif');
        $UserChange->admin = $http->post('Admin');
        $UserChange->email = $http->post('email');
        if ($UserChange->active ==-1 || $UserChange->admin ==-1)
        {
            die ('Missing data');
        }
        if (  trim($_POST['password'])<>'')
        {
            $UserChange->setPassword(md5($_POST['password']));
            $UserChange->save();
        }
        else
	{
            $UserChange->save();
	}

    }
}
else if ($sbaction == "delete")
{
/******************************************************/
// Delete the user
/******************************************************/
    // check that the control is correct
    try {
        $code=$http->post("userdel");
        $ctl_code=$http->post('ctlcode');
        $uid = $http->request('use_id');
    } catch (Exception $ex) {
         echo_error($ex->getMessage());
         throw $ex;
    }
    if ( DEBUGNOALYSS > 1) {
        echo "code [$code] code control [$ctl_code]";
    }
    if ( $code != $ctl_code) {
        echo _("Code invalide, effacement refusé");
        return;
    }
    $cn = new Database();
    $auser=$cn->get_row('select use_login from ac_users where use_id = $1',[$uid]);
    if ( $auser == null) return;
    $Res = $cn->exec_sql("delete from jnt_use_dos where use_id=$1", array($uid));
    $Res = $cn->exec_sql("delete from ac_users where use_id=$1", array($uid));
    //------------------------------------
    // Remove user from all the dossiers
    //------------------------------------
    $a_dossier=$cn->get_array('select dos_id from ac_dossier');
    if ( is_array($a_dossier) ) {
        $nb=count($a_dossier);
        for ( $i=0;$i<$nb;$i++)
            User::remove_inexistant_user($a_dossier[$i]['dos_id']);
    }
    User::audit_admin(sprintf('DELETE USER %s %s',$uid,$auser['use_login']));
    echo "<H2 class=\"notice\">";
    printf (_("Utilisateur %s %s est effacé"),$http->post('fname'),$http->post('lname')) ;
    echo " </H2>";
}
// View user detail
if ( isset($_REQUEST['det']) && $sbaction=="")
{
    require_once NOALYSS_INCLUDE.'/user_detail.inc.php';

    return;
}
?>

<div id="create_user" style="display:none;width:30%;margin-right: 20%" class="inner_box">
<?php echo HtmlInput::title_box(_('Ajout Utilisateur'),"create_user","hide");?>
    <form action="admin-noalyss.php?action=user_mgt" method="POST" onsubmit="return check_form()">
    <div style="text-align: center">
<TABLE class="result" >            
       <TR><TD style="text-align: right"> <?php echo _('login')?></TD><TD><INPUT id="input_login" class="input_text"  TYPE="TEXT" NAME="LOGIN"></TD></tr>
        <TR><TD style="text-align: right"> <?php echo _('Prénom')?></TD><TD><INPUT class="input_text" TYPE="TEXT" NAME="FNAME"></TD></tr>
       <TR><TD style="text-align: right"> <?php echo _('Nom')?></TD><TD><INPUT class="input_text"  TYPE="TEXT" NAME="LNAME"></TD></TR>
       <TR><TD style="text-align: right"> <?php echo _('Mot de passe')?></TD><TD> <INPUT id="input_password" class="input_text" TYPE="TEXT" NAME="PASS"></TD></TR>
       <TR><TD style="text-align: right"> <?php echo _('Email')?></TD><TD> <INPUT class="input_text" TYPE="TEXT" NAME="EMAIL"></TD></TR>
</TABLE>
<?php
echo HtmlInput::submit("ADD",_('Créer Utilisateur'),"",'button');
echo HtmlInput::button_action(_("Fermer"), "$('create_user').style.display='none';");

?>
</div>
</FORM>
    <script>
        function check_form() {
            if ($F('input_login') == "") { 
                    smoke.alert('<?php echo _('Le login ne peut être vide') ?>');
                    $('input_login').setStyle({border:"red solid 2px"});
                    return false;
                }
            if ($F('input_password') == "") { 
                smoke.alert('<?php echo _('Le mot de passe ne peut être vide') ?>');
                $('input_password').setStyle({border:"red solid 2px"});
                return false;
            }
            return true;
        }
    </script>
</div>

<?php
echo '<p>';
echo HtmlInput::button_action(_("Ajout utilisateur"), "$('create_user').show();","cu");
echo '</p>';
// Show all the existing user on 7 columns
$repo=new Dossier(0);
/******************************************************/
// Detail of a user
/******************************************************/



$compteur=0;
$header=new Sort_Table();
$url=basename($_SERVER['PHP_SELF'])."?action=".$_REQUEST['action'];
$header->add(_("Login"), $url," order by use_login asc", "order by use_login desc","la", "ld");
$header->add(_("Nom"), $url," order by use_name asc,use_first_name asc", "order by use_name desc,use_first_name desc","na", "nd");
$header->add(_('Dossier'),$url,' order by ag_dossier asc','order by ag_dossier desc',
        'da','dd');
$header->add(_("Actif"), $url," order by use_active asc", "order by  use_active desc","aa", "ad");
$ord=(isset($_REQUEST['ord']))?$_REQUEST['ord']:'la';
$sql=$header->get_sql_order($ord);

$a_user=$repo->get_user_folder($sql);

if ( !empty ($a_user) )
{
	echo '<span style="display:block">';
	echo _('Cherche').Icon_Action::infobulle(22);
	echo HtmlInput::filter_table("user", "0,1,2,5","1");
	echo '</span>';
    echo '<table id="user" class="result">';
    echo '<tr>';
    echo '<th>'.$header->get_header(0).'</th>';
    echo '<th>'.$header->get_header(1).'</th>';
    echo th(_("Prénom"));
    echo '<th>'.$header->get_header(3).'</th>';
	echo "<th>"._('Type')."</th>";
    echo '<th>'.$header->get_header(2).'</th>';
    echo '</tr>';

    foreach ( $a_user as $r_user)
    {
        $compteur++;
        $class=($compteur%2==0)?"odd":"even";

        echo "<tr class=\"$class\">";
        if ( $r_user['use_active'] == 0 )
        {
            $Active=$g_failed;
        }
        else
        {
            $Active=$g_succeed;
        }
        $det_url=$url."&det&use_id=".$r_user['use_id'];
        echo "<td>";
        echo HtmlInput::anchor($r_user['use_login'],$det_url);
        echo "</td>";

        echo td($r_user['use_name']);
        echo td($r_user['use_first_name']);
        echo td($Active);
        $type=($r_user['use_admin']==1)?_("Administrateur"):_("Utilisateur");
        echo "<td>".$type."</td>";
        if ( $r_user['use_admin'] == 0)
            echo td($r_user['ag_dossier']);
        else {
            echo td(_('Tous'));
        }
        echo '</tr>';
    }// foreach
    echo '</table>';
} // $cn != null
?>

</div>