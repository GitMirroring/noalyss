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
 *\file
 * \brief file included include the purchase operation
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
$gDossier = dossier::id();
global $g_parameter;
$http=new HttpInput();

$cn = Dossier::connect();
//menu = show a list of ledger
$str_dossier = dossier::get();
$ac=$http->request("ac");

$request_jrn=$http->request("p_jrn", "string","");
// Check privilege
if ($request_jrn !="" && 
    $g_user->check_jrn($request_jrn) != 'W')
{
        NoAccess();
        exit - 1;
}
$p_msg="";
$post_jrn=$http->post("p_jrn", "string","");
/* if a new invoice is encoded, we display a form for confirmation */
if (isset($_POST['view_invoice']))
{
	$Ledger = new Acc_Ledger_Purchase($cn, $post_jrn);
	try
	{
		$Ledger->verify_operation($_POST);
	}
	catch (Exception $e)
	{
		alert($e->getMessage());
                $p_msg=$e->getMessage();
		$correct = 1;
	}
	// if correct is not set it means it is correct
	if (!isset($correct))
	{
		echo '<div class="content">';
		echo '<div id="confirm_div_id" style="width: 47%; float: left;">';
                echo h1(_("Confirmation"));
                echo span(_("Vous devez encore confirmer"),' class="notice"');
                echo '</div>';

		echo '<div id="confirm_div_id" style="width: 100%; float: left;">';
                echo '<form enctype="multipart/form-data" method="post" class="print">';
		echo dossier::hidden();

		echo $Ledger->confirm($_POST);
		echo HtmlInput::hidden('ac', $ac);
                $Ledger->input_extra_info();
                echo '<div class="bt-center">';
                echo '<ul class="aligned-block">';
                echo '<li>';
                echo HtmlInput::submit("record", _("Confirmer"), 'onClick="return verify_ca(\'\');"', p_class: "button");
                echo '</li>';
                echo '<li>';
                echo HtmlInput::submit('correct', _("Corriger"), p_class: "button");
                echo '</li>';
                echo '</ul>';
                echo '</div>';
                echo '</form>';
                echo '</div>';
                if (DEBUGNOALYSS>1) { echo "<!-- confirm_div_id -->";}
            return;
	}
}
//------------------------------
/* Record the invoice */
//------------------------------

if (isset($_POST['record']))
{
	$Ledger = new Acc_Ledger_Purchase($cn, $post_jrn);
	try
	{
		$Ledger->verify_operation($_POST);
	}
	catch (Exception $e)
	{
		alert($e->getMessage());
                $p_msg=$e->getMessage();
		$correct = 1;
	}
	// record the invoice
	if (!isset($correct))
	{
		echo '<div class="content">';

		$Ledger = new Acc_Ledger_Purchase($cn, $post_jrn);

        try {
		    $internal = $Ledger->insert($_POST);
                    $Ledger->upload_supplemental_document($Ledger->jr_id);
        } catch (\Exception $e) {
            if ( $e->getCode()==EXC_BALANCE)
            {
                \record_log($e);
                echo_warning(_("enregistrement annulé: balance , voyer le fichier log"));
            }
            else
            {
                echo_warning($e->getMessage());
            }
            return;
        }


		/* Save the predefined operation */
                if ( isset($_POST['opd_name']) && trim($_POST['opd_name']) != "" )
		{
			$opd = new Pre_operation($cn);
			$opd->get_post();
			$opd->save();
		}

		/* Show button  */
		$jr_id = $cn->get_value('select jr_id from jrn where jr_internal=$1', array($internal));

		echo '<h1> '._('Enregistrement').' </h1>';
		/* Save the additional information into jrn_info */
		$obj = new Acc_Ledger_Info($cn);
		$obj->save_extra($Ledger->jr_id, $_POST);
                
                /* save followup */
                $Ledger->save_followup($http->request("action_gestion","string",""));
                
		// Feedback
		echo $Ledger->confirm($_POST, true);
		if (isset($Ledger->doc))
		{
                     echo '<h2>'._('Document').'</h2>';
                     echo $Ledger->doc;
		}
                // extourne
                if (isset($_POST['reverse_ck']))
                {
                    $p_date=$http->post('reverse_date','string', '');
                    $p_msg=$http->post("ext_label");
                    if (isDate($p_date)==$p_date)
                    {
                        // reverse the operation
                        try
                        {
                            $Ledger->reverse($p_date,$p_msg);
                            echo '<p>';
                            printf ( _('Extourné au %s'),$p_date);
                            echo '</p>';
                        }
                        catch (Exception $e)
                        {
                            echo '<p class="error">'.
                                    _('Opération non extournée').
                                    " ".
                                $e->getMessage().
                                '</p>';
                        }
                    }
                    else
                    {
                        // warning because date is invalid
                        echo '<span class="warning">'._('Date invalide, opération non extournée').'</span>';
                    }
                }
                echo '<div class="bt-center">';
                echo '<ul class="aligned-block">';
                echo "<li>";
                echo $Ledger->button_new_operation();
                echo "</li>";
                echo "<li>";
                echo $Ledger->button_copy_operation();
                echo "</li>";
                echo "</ul>";
                echo '</div>';
                echo '</div>';
		return;
	}
}
//  ------------------------------------------------------------
/* Display a blank form or a form with predef operation */
/* or a form for correcting */
//  -------------------------------------------------------------

//


$array = (isset($_POST['correct']) || isset($correct)) ? $_POST : null;
$Ledger = new Acc_Ledger_Purchase($cn, 0);


if (!isset($_REQUEST ['p_jrn']))
{
	$def_ledger = $Ledger->get_first('ach',2);
	if ( empty ($def_ledger))
	{
		exit(_('Pas de journal disponible'));
	}
	$Ledger->id = $def_ledger['jrn_def_id'];
}
else if (isset($_REQUEST ['p_jrn']))
	$Ledger->id = $request_jrn;
else if (isset ($_REQUEST['p_jrn_predef'])){
	$Ledger->id=$http->request('p_jrn_predef');
}
// pre defined operation
//


echo '<div class="content">';

if ( $p_msg !="" ) echo '<span class="warning">'.$p_msg.'</span>';

try
{
    $payment=$http->request("e_mp", "string",0);
    $date_payment=$http->request("mp_date", "string","");
    $comm_payment=$http->request("e_comm_paiement", "string","");
    $acompte=$http->request("acompte", "string",0);

    echo "<FORM class=\"print\"NAME=\"form_detail\" METHOD=\"POST\" >";
    echo HtmlInput::hidden("ac", $ac);
    /* request for a predefined operation */
    if (isset($_REQUEST['pre_def'])&&!isset($_POST['correct']) && ! isset($correct) )
    {
        // used a predefined operation
        $predef=$http->request("pre_def","string", "0");
        $p_jrn_predef=$http->request("p_jrn_predef","string", "0");
        $op=new Pre_operation($cn);
        $op->set_od_id($predef);
        $p_post=$op->compute_array();
        $Ledger->id=$p_jrn_predef;
        $p_post['p_jrn']=$Ledger->id;
        echo $Ledger->input($p_post);
        echo '<div class="content">';
        echo $Ledger->input_paid($payment,$acompte,$date_payment,$comm_payment);
        echo '</div>';
        echo '<script>';
        echo 'compute_all_ledger();';
        echo '</script>';
    }
    else if (isset($_GET['create_feenote']))
    {
        $action_id=$http->get('ag_id',"number");
        $cp=$http->get('cp','number',0);
        $array=$Ledger->convert_from_follow($action_id,$cp);
        echo HtmlInput::hidden("ledger_type", "VEN");
        echo HtmlInput::hidden("ac",$http->get('ac'));
        echo HtmlInput::hidden("sa", "p");
        echo HtmlInput::hidden("action_gestion",$action_id);
        echo $Ledger->input($array);
        echo '<div class="content">';
        echo $Ledger->input_paid($payment,$acompte,$date_payment,$comm_payment);
        echo '</div>';
        echo '<script>';
        echo 'compute_all_ledger();';
        echo '</script>';
    }
    else
    {
        echo $Ledger->input($array);
        echo HtmlInput::hidden("p_action", "ach");
        echo HtmlInput::hidden("sa", "p");
        echo '<div class="content">';
        echo $Ledger->input_paid($payment,$acompte,$date_payment,$comm_payment);
        echo '</div>';
        echo '<script>';
        echo 'compute_all_ledger();';
        echo '</script>';
    }
    echo '<div class="content">';
    echo '<div class="bt-center">';
    echo '<ul class="aligned-block">';
    echo '<li>';
    echo HtmlInput::button('act', _('Actualiser'), 'onClick="compute_all_ledger();"', p_class: "button");
    echo '</li>';
    echo '<li>';
    echo HtmlInput::submit("view_invoice", _("Enregistrer"), p_class: "button");
    echo '</li>';
    echo '<li>';
    echo HtmlInput::reset(_('Effacer '), p_class: "button");
    echo '</li>';
    echo '</ul>';
    echo '</div>';

    echo '</div>';
    echo "</FORM>";
}
catch (Exception $e)
{
    alert($e->getMessage());
    return;
}
$e_date=$http->request("e_date","string","");

if ($e_date=="" && $g_parameter->MY_DATE_SUGGEST=='Y')
  {
    echo create_script(" get_last_date()");
  }
echo create_script(" update_name()");
echo '</div>';


return;
// end record invoice
?>
