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
/**\file
 * \brief this file is always included and then executed
 *        it permits to change the user preferences
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');


global $g_user;

$g_user=new User($cn);
$inside_dossier = false;
$http=new HttpInput();
$action=$http->post("action","string","display_form");

if (isset($_REQUEST['gDossier']) && $http->request("gDossier","number",0) != 0 )
{
        $g_user->load_global_pref();
        $msg = "";
        $cn =Dossier::connect();
        $g_user->cn = $cn;
        $inside_dossier = true;
        $local_pref=$g_user->get_preference();
}
//////////////////////////////////////////////////////////////////////////
// Theme
//////////////////////////////////////////////////////////////////////////

    $repo = new Database();
// charge tous les styles
    $res = $repo->exec_sql("select the_name from theme
                    order by the_name");
    $style = new ISelect('style_user');
    $style->value = $repo->make_array("select the_name,the_name
	from theme
	order by the_name");
    $style->selected =$_SESSION[SESSION_KEY.'g_theme'];
    
//----------------------------------------------------------------------------------------------
// Display the form    
//----------------------------------------------------------------------------------------------    
if ( $action == 'display_form' )    
{
    echo HtmlInput::title_box(_('Préférence'), 'preference_div');
    echo '<DIV class="content">';
    echo '<p class="notice">';
    echo _("Après validation, recharger pour appliquer les changements");
    echo '</p>';
    //----------------------------------------------------------------------
    //
?>

<div class="content" >

    <FORM  METHOD="POST" onsubmit="updatePreference();return false;" id="preference_frm">
	<fieldset style="margin: 1%"><legend><?php echo _('Options Générales')?></legend>
	    <table>
                <tr>
                    <td>
                        <?php echo _('Email')?>
                    </td>
                    <td>
                        <input type="text" name="p_email" value="<?php echo $g_user->email?>" class="input_text">
                    </td>
                </tr>
		<tr><td>
			Mot de passe :
		    </td>
		    <td><input type="password" value="" class="input_text" name="pass_1" nohistory>
			<input type="password" value="" class="input_text" name="pass_2" nohistory>
		    </td>
		</tr>

		<tr>
		    <td>
			<?php echo _('Thème');?>
		    </td>
		    <td>
			<?php echo $style->input();?>
		    </td>
		</tr>

		<?php
		if ($inside_dossier)
		{
		    $l_user_per = $g_user->get_periode();
		    if ($l_user_per == "")
			$l_user_per = $cn->get_value("select min(p_id) from parm_periode where p_closed='f'");

                    // if periode is closed then warns the users
		    $period = new Periode($cn, $l_user_per);

		    $period->p_id = $l_user_per;
		    $period->jrn_def_id = 0;
                    $selected_exercice=$period->get_exercice();
                    $js=sprintf('onchange="updatePeriodePreference(%d);"',Dossier::id());
                    $exercice=new Exercice($cn);
                    
		    if ($period->is_closed($l_user_per) == 1)
		    {
			$msg = _('Attention cette période est fermée, vous ne pourrez rien modifier dans le module comptable');
			$msg = '<h2 class="notice">' . $msg . '</h2>';
		    }
                    
		    $iperiod = new IPeriod("period");
                    $iperiod->id="setting_period";
		    $iperiod->user = $g_user;
		    $iperiod->cn = $cn;
		    $iperiod->filter_year = true;
                    $iperiod->exercice=$selected_exercice;
		    $iperiod->value = $l_user_per;
		    $iperiod->type = ALL;
		    $l_form_per = $iperiod->input();
		    ?>
                <tr>
                    <td>
                        <?=_("Exercice")?>
                    </td>
                    <td>
                        <?=$exercice->select("exercice_setting",$selected_exercice,$js)->input();?>
                    </td>
                </tr>
          
    		<tr>
                    
                    <td><?php echo _('Période');?></td>
    		    <td>
			    <?php printf(' %s ', $l_form_per);?>
    		    </td>
    		    <td>  <?php echo $msg;?></td>
    		<tr>
    		    <td><?php echo _('Taille des pages');?></td>
    		    <td>
    			<SELECT NAME="p_size">
    			    <option value="15">15
    			    <option value="25">25
    			    <option value="50">50
    			    <option value="100">100
    			    <option value="150">150
    			    <option value="200">200
    			    <option value="-1"><?php echo _('Illimité');?>
				    <?php
				    $label = ($_SESSION[SESSION_KEY.'g_pagesize'] == -1) ? _('Illimité') : $_SESSION[SESSION_KEY.'g_pagesize'];
				    echo '<option value="' . $_SESSION[SESSION_KEY.'g_pagesize'] . '" selected>' . $label;
				    ?>
    			</SELECT>

    		    </td>
    		</tr>
		    <?php 
		}
		?>
                  <tr>
                <td>
                    <?=_("Premier jour semaine")?>
                </td>
                <td>
                    <?php
                        $aFirstDay=array(
                            ["label"=>_("Lundi"),"value"=>1],
                            ["label"=>_("Mardi"),"value"=>2],
                            ["label"=>_("Mercredi"),"value"=>3],
                            ["label"=>_("Jeudi"),"value"=>4],
                            ["label"=>_("Vendredi"),"value"=>5],
                            ["label"=>_("Samedi"),"value"=>6],
                            ["label"=>_("Dimanche"),"value"=>0],
                        );
                        $selFirstDay=new ISelect("selFirstDay");
                        $selFirstDay->value=$aFirstDay;
                        $selFirstDay->selected=$g_user->get_first_week_day();
                        echo $selFirstDay->input();
                    ?>

                </td>
            </tr>
	    </table>
	</fieldset>
        <fieldset>
            <legend><?=_("Format Export CSV")?></legend>
            <p>
                <?php 
                if ( $_SESSION[SESSION_KEY.'csv_fieldsep']==1 && $_SESSION[SESSION_KEY.'csv_decimal']==1)
                {
                 echo_warning(_("N'utilisez pas le même séparateur pour les champs et les décimales"));
                }
                ?>
            </p>
            <table>
                <tr>
                    <td>
                        <?=_("Séparateur de champs")?>
                    </td>
                    <td>
                        <?php
                            $csv_fieldsep=new ISelect('csv_fieldsep');
                            $csv_fieldsep->value=[
                                ["label"=>_("Point-virgule"),"value"=>0],
                                ["label"=>_("virgule"),"value"=>1]
                            ];
                            $csv_fieldsep->selected=$_SESSION[SESSION_KEY.'csv_fieldsep'];
                            echo $csv_fieldsep->input();
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?=_("Séparateur Décimale")?>
                    </td>
                    <td>
                        <?php
                            $csv_decimal=new ISelect('csv_decimal');
                            $csv_decimal->value=[
                                ["label"=>_("point"),"value"=>0],
                                ["label"=>_("virgule"),"value"=>1]
                            ];
                            $csv_decimal->selected=$_SESSION[SESSION_KEY.'csv_decimal'];
                            echo $csv_decimal->input();
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?=_("Encodage")?>
                    </td>
                    <td>
                        <?php
                            $csv_encoding=new ISelect('csv_encoding');
                            $csv_encoding->value=[
                                ["label"=>_("utf8"),"value"=>'utf8'],
                                ["label"=>_("latin1"),"value"=>'latin1']
                            ];
                            $csv_encoding->selected=$_SESSION[SESSION_KEY.'csv_encoding'];
                            echo $csv_encoding->input();
                        ?>
                    </td>
                </tr>
            </table>
                
        </fieldset>
	<?php
	if ($inside_dossier)
	{
	    /* Pref for welcome page */
	    echo '<fieldset style="margin: 1%">';
	    echo '<legend>' . _('Options pour la page d\'accueil') . '</legend>';
	    echo _('Mini-Rapport : ');
	    $rapport = new Acc_Report($cn);
	    $aRapport = $rapport->make_array();
	    $aRapport[] = array("value" => 0, "label" => _('Aucun mini rapport'));
	    $wRapport = new ISelect();
	    $wRapport->name = "minirap";
	    $wRapport->selected = $g_user->get_mini_report();
	    $wRapport->value = $aRapport;
	    echo $wRapport->input();
	    echo '<span class="notice">' . _('Le mini rapport est un rapport qui s\'affiche  sur votre page d\'accueil') . '</span>';
	    echo '</fieldset>';
	}

	echo '<fieldset  style="margin: 1%">';
	echo '<legend>' . _('Langue') . '</legend>';
	echo _('Selectionnez votre langue');
	$aLang = array(array(_('Français'), 'fr_FR.utf8'),
	    array(_('Anglais'), 'en_US.utf8'),
	    array(_('Néerlandais'), 'nl_NL.utf8'),
	);
	echo '<select name="lang" id="l">';
	for ($i = 0; $i < count($aLang); $i++)
	{
	    $sel = "";
	    if ($aLang[$i][1] == $_SESSION[SESSION_KEY.'g_lang'])
		$sel = " selected ";
	    printf('<option value="%s" %s>%s</option>', $aLang[$i][1], $sel, $aLang[$i][0]);
	}
	echo '</select>';
	echo '</fieldset>';
        
        if  ($inside_dossier){
            echo Dossier::hidden();
        }
        echo '<p style="text-align:center">';
	echo HtmlInput::button_close('preference_div');
	echo HtmlInput::submit("set_preference", _("Valider"));
        echo '</p>';
	echo '</form>';

	echo "</DIV>";
	?>
<?php
}
//---------------------------------------------------------------------------------------------------------------------
// Save the form
//---------------------------------------------------------------------------------------------------------------------
if ($action == 'save')
{
       //// Save value
    $style_user=$http->post("style_user","string","Classique");
    $lang=$http->post("lang","string","fr_FR.utf8");
    $p_size=$http->post("p_size","number",50);
    $pass_1=$http->post("pass_1","string","");
    $pass_2=$http->post("pass_2","string","");
    $p_email=$http->post("p_email","string","");
    $csv_fieldsep=$http->post("csv_fieldsep","number");
    $csv_decimal=$http->post("csv_decimal","number");
    $csv_encoding=$http->post("csv_encoding");
    $firstday=$http->post("selFirstDay","number");
    
    if (strlen(trim($pass_1)) != 0 && strlen(trim($pass_2)) != 0)
    {
        if ( $g_user->save_password($_POST['pass_1'],$pass_2) ) 
        {        $g_user->password_to_session() ;
        
        } else {
           /**
            * password not changed
            */ 
            
        }
        
        
    }
    if ( $inside_dossier)
    {
        $minirap=$http->post("minirap","number","0");
        $period=$http->post("period","number");
        $g_user->set_periode($period);
        $g_user->set_mini_report($minirap);
    }
    $g_user->save_global_preference('THEME', $style_user);
    $g_user->save_global_preference('LANG', $lang);
    $g_user->save_global_preference('PAGESIZE', $p_size);
    $g_user->save_global_preference('csv_fieldsep', $csv_fieldsep);
    $g_user->save_global_preference('csv_decimal', $csv_decimal);
    $g_user->save_global_preference('csv_encoding', $csv_encoding);
    $g_user->save_global_preference('first_week_day', $firstday);
    $g_user->save_email($p_email);

    $_SESSION[SESSION_KEY.'g_theme']=$style_user;
    $_SESSION[SESSION_KEY.'g_pagesize']=$p_size;
    $_SESSION[SESSION_KEY.'g_lang']=$lang;
    
    // find the right CSS theme
    $style= $repo->get_value("select the_filestyle from theme
                           where the_name=$1" ,[$style_user]);
    if ($style == "")
    {
        $style = "style-classic7.css";
    }
    json_response(["style"=>$style]);
    
}
