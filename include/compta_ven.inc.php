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

/*!\file
 * \brief file included to manage all the operations for the ledger of sales 
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');

$gDossier=dossier::id();
$cn=Dossier::connect();
//menu = show a list of ledger
$str_dossier=dossier::get();
global $g_parameter;
$http=new HttpInput();
$strac=$http->request('ac');
$ac="ac=".$strac;
$p_msg="";
$post_jrn=$http->post("p_jrn", "string","");
//----------------------------------------------------------------------
// Encode a new invoice
// empty form for encoding
//----------------------------------------------------------------------

    $Ledger=new Acc_Ledger_Sale($cn,0);

    // Check privilege
    if ( isset($_REQUEST['p_jrn']) &&
            $g_user->check_jrn($http->request("p_jrn","number")) != 'W' )
    {

        NoAccess();
        exit -1;
    }

    /* if a new invoice is encoded, we display a form for confirmation */
    if ( isset ($_POST['view_invoice'] ) )
    {
        $p_jrn=$http->post("p_jrn","number");
        $Ledger=new Acc_Ledger_Sale($cn,$p_jrn);
        try
        {
            $Ledger->verify_operation($_POST);
        }
        catch (Exception $e)
        {
            alert($e->getMessage());
            $p_msg=$e->getMessage();
            $correct=1;
        }
        // if correct is not set it means it is correct
        if ( ! isset($correct))
        {
            echo '<div class="content">';

            echo '<div id="confirm_div_id" style="width: 47%; float: left;">';
            echo h1(_("Confirmation"));
            echo span(_("Vous devez encore confirmer"),' class="notice"');
            echo '</div>';
            
            echo '<div id="confirm_div_id" style="width: 100%; float: left;">';
            echo '<form class="print" enctype="multipart/form-data" method="post">';
            echo dossier::hidden();
            echo $Ledger->confirm($_POST );
            echo HtmlInput::hidden('ac',$strac);
            $Ledger->input_extra_info();
            echo HtmlInput::submit("record", _("Enregistrement"), 'onClick="return verify_ca(\'\');"');
            echo HtmlInput::submit('correct', _("Corriger"));
            echo '</form>';
            echo '</div>';
            if (DEBUGNOALYSS>1) { echo "<!-- confirm_div_id -->";}
            return;
        }
    }
    //------------------------------
    /* Record the invoice */
    //------------------------------

    if ( isset($_POST['record']) )
    {
// Check privilege
        if ( $g_user->check_jrn($post_jrn) != 'W' )
        {

            NoAccess();
            exit -1;
        }

        $Ledger=new Acc_Ledger_Sale($cn,$post_jrn);
        try
        {
            $Ledger->verify_operation($_POST);
        }
        catch (Exception $e)
        {
            alert($e->getMessage());
            $correct=1;
        }

        if ( ! isset($correct))
        {
            if ( is_msie() == 0 ) 
                echo '<div style="position:absolute"  class="content">';
             else
                echo '<div class="content">';

            $Ledger=new Acc_Ledger_Sale($cn,$_POST['p_jrn']);
            $internal=$Ledger->insert($_POST);

            /* Save the predefined operation */
            if ( isset($_POST['opd_name']) && trim($_POST['opd_name']) != "" )
            {
                $opd=new Pre_operation($cn);
                $opd->get_post();
                $opd->save();
            }

            /* Show button  */
            echo '<h1> Enregistrement </h1>';

            echo $Ledger->confirm($_POST,true);
            /* Show link for Invoice */
            if (isset ($Ledger->doc) )
            {
                echo '<h2>'._('Document').' </h2>';
                echo $Ledger->doc;
            }


            /* Save the additional information into jrn_info */
            $obj=new Acc_Ledger_Info($cn);
            $obj->save_extra($Ledger->jr_id,$_POST);
            
             /* save followup */
             $Ledger->save_followup($http->request("action_gestion","string",""));
             
             // extourne
            if (isset($_POST['reverse_ck']))
            {
                $p_date=$http->post('reverse_date', "string",'');
                $p_msg=$http->post("ext_label");
                if (isDate($p_date)==$p_date)
                {
                    // reverse the operation
                    try
                    {
                        $Ledger->reverse($p_date,$p_msg);
                        echo '<p>';
                        echo _('Extourné au ').$p_date;
                        echo '</p>';

                    }
                    catch (Exception $e)
                    {
                        echo '<span class="warning">'._('Opération non extournée').
                            $e->getMessage().
                            '</span>';
                    }
                }
                else
                {
                    // warning because date is invalid
                    echo '<span class="warning">'._('Date invalide, opération non extournée').'</span>';
                }
            }
            echo '<ul class="aligned-block">';
            echo "<li>";
            echo $Ledger->button_new_operation();
            echo "</li>";
            echo "<li>";
            echo $Ledger->button_copy_operation();
            echo "</li>"; 
            echo "</ul>";
            echo '</div>';
            return;
        }
    }
//  ------------------------------
/* Display a blank form or a form with predef operation */
//  ------------------------------

$array=(isset($_POST['correct'])||isset ($correct))?$_POST:null;
$Ledger=new Acc_Ledger_Sale($cn,0);
//
// pre defined operation
//
echo '<div class="content">';

if (!isset($_REQUEST ['p_jrn']))
{
    $def_ledger=$Ledger->get_first('ven', 2);
    if (empty($def_ledger))
    {
        exit(_('Pas de journal disponible'));
    }
    $Ledger->id=$def_ledger['jrn_def_id'];
}
else
    $Ledger->id=$http->request('p_jrn');

if (isset($_REQUEST['p_jrn_predef']))
{
    $Ledger->id=$http->request('p_jrn_predef');
}


echo '<div class="content">';
if ($p_msg!="")
{
    echo '<span class="warning">'.$p_msg.'</span>';
}
try
{
    $payment=$http->request("e_mp","string", 0);
    $date_payment=$http->request("mp_date", "string","");
    $comm_payment=$http->request("e_comm_paiement", "string","");
    $acompte=$http->request("acompte", "string",0);
    
    echo "<FORM class=\"print\" NAME=\"form_detail\" METHOD=\"POST\" >";
    /* request for a predefined operation */
    if (isset($_REQUEST['pre_def'])&&!isset($_POST['correct']) && ! isset($correct))
    {
        // used a predefined operation
        //
       $op=new Pre_operation($cn);
        $op->set_od_id($http->request('pre_def'));
        $p_post=$op->compute_array();
        $Ledger->id=$http->request('p_jrn_predef');

        echo $Ledger->input($p_post);
        echo '<div class="content">';
        echo $Ledger->input_paid($payment);
        echo '</div>';
        echo '<script>';
        echo 'compute_all_ledger();';
        echo '</script>';
    }
    else if (isset($_GET['create_invoice']))
    {
        $array=$Ledger->convert_from_follow($_GET ['ag_id']);
        echo HtmlInput::hidden("ledger_type", "VEN");
        echo HtmlInput::hidden("ac", $_REQUEST['ac']);
        echo HtmlInput::hidden("sa", "p");
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
        echo HtmlInput::hidden("ledger_type", "VEN");
        echo HtmlInput::hidden("ac", $strac);
        echo HtmlInput::hidden("sa", "p");

        echo $Ledger->input($array);
        echo '<div class="content">';
        echo $Ledger->input_paid($payment,$acompte,$date_payment,$comm_payment);
        echo '</div>';
        echo '<script>';
        echo 'compute_all_ledger();';
        echo '</script>';
    }
}
catch (Exception $e)
{
    alert($e->getMessage());
    return;
}
echo '<div class="content">';


    echo HtmlInput::button('act',_('Actualiser'),'onClick="compute_all_ledger();"');
    echo HtmlInput::submit("view_invoice",_("Enregistrer"));
    echo HtmlInput::reset(_('Effacer '));
    echo '</div>';
    echo "</FORM>";

    /* if we suggest the pj n# the run the script */
    if ( $g_parameter->MY_PJ_SUGGEST=='Y')
    {
		echo '<script> update_pj()</script>';
    }
	if (!isset($_REQUEST['e_date']) && $g_parameter->MY_DATE_SUGGEST=='Y')
	{
		echo create_script(" get_last_date()");
	}
echo create_script(" update_name()");	
return;
?>
