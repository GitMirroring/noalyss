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
\Noalyss\Dbg::echo_file(__FILE__);
$gDossier=dossier::id();
$cn=Dossier::connect();
//menu = show a list of ledger
$str_dossier=dossier::get();
global $g_parameter;
$http=new HttpInput();
$strac=$http->request('ac');
$ac="ac=".$strac;
$p_msg="";
//@var $post_jrn (int) Ledger id JRN_DEF.JRN_DEF_ID
$post_jrn=$http->post("p_jrn", "number","");
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
//------------------------------------------------
/* if a new invoice is encoded, we display a form for confirmation */
//------------------------------------------------
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
    //------------------------------------------------
    // Confirm before saving
    //------------------------------------------------
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
        //----------------------------------------------------
        //  Check that INVOICE can be generated 
        //  for e-invoice only 
        //----------------------------------------------------
        if ($g_parameter->MY_INVOICE_FORMAT != 'BASIC')
        {
            $xmldocument= \Noalyss\XMLDocument\XMLInvoice::build_xmlinvoice($cn);
            $array=[];
            $array['supplier']=$xmldocument->fill_supplier();
            $customer=Fiche::from_qcode($cn,trim($http->post("e_client")));
            
            $array['customer']=$xmldocument->fill_customer($customer->id);
            $array['operation']=$xmldocument->fill_operation_from_array($_POST);
            $array['due_date']=$http->post("e_ech");
            if (  $array['due_date'] == '') 
            {
                $array['due_date']=$http->post("e_date");
            }
            $xmldocument->set_data($array);
            $xmldocument->display_error();
        }
        
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

        $Ledger=new Acc_Ledger_Sale($cn,$post_jrn);
        try {
            $internal=$Ledger->insert($_POST);
            $Ledger->upload_supplemental_document($Ledger->jr_id);
            // var $receipt (string) contains the name of the file name of 
            //              the invoice (document created), if empty there
            //              is no invoice
            
            $receipt='';
            //-------------------------------------------------------
            // Generate a XLM invoice
            // if a document has been created create the XML file 
            //-------------------------------------------------------
            ///@var $flag_invoice (int) error for invoice generating. 
            ///                     0 = nothing no invoice created
            ///                     1 = cannot create e-invoice
            ///                     2 = create e-invoice requested

            $flag_invoice=0;
             /* Save the attachment or generate doc */
            if (isset($_FILES['pj']) && noalyss_strlentrim($_FILES['pj']['name']) != 0)
            { 
                $acc_document=new Acc_Document($cn,$Ledger->jr_id);
                $acc_document->save_receipt();
                $receipt= HtmlInput::show_receipt_document($Ledger->jr_id
                        ,h($_FILES['pj']['name']));
            }
            else
                /* Generate an invoice and save it into the database */
           if (isset($_POST['gen_invoice'])) 
            {
                // generate an invoice
                $file = $Ledger->create_document($internal, $_POST);
                $receipt= HtmlInput::show_receipt_document($Ledger->jr_id
                        ,h($file));
                $acc_document=new Acc_Document($cn,$Ledger->jr_id);

                if ($g_parameter->MY_INVOICE_FORMAT != 'BASIC' && ! empty($acc_document->d_filename ))
                {
                    $flag_invoice=2;
                    $xmldocument= \Noalyss\XMLDocument\XMLInvoice::build_xmlinvoice($cn);
                    $xmldocument->build_data($Ledger->jr_id);
                    $code_error = $xmldocument->verify() ;
                    // check that all the sub arrays are empty
                    if ( ! empty( array_filter($code_error,function($a){ if (!empty($a)) return true; })))  
                    {
                        $xmldocument->display_error();
                        $flag_invoice=1;
                    }
                }
                //------------------------------------------------
                // flag_invoice == 2 , generate an e-invoice
                //------------------------------------------------
                if ( $flag_invoice == 2 ) 
                {    
                    $pdf_filename=$acc_document->d_filename;
                    if ( $acc_document->d_mimetype != 'application/pdf')
                    {
                        $pdf_filename=$acc_document->transform2pdf();
                   
                        // save PDF In db
                        $acc_document->update($pdf_filename);
                    }else{
                        $pdf_filename=$_ENV['TMP']."/".$pdf_filename;
                        $acc_document->export_file($pdf_filename);
                    }
                    // make the PDF 
                    $xmldocument->set_pdf_filename($pdf_filename);
                        
                    // make the XML  + PDF 
                    //@var $xml(XML String)
                    $xml=$xmldocument->create_invoice($Ledger->jr_id);
                    if (DEBUGNOALYSS > 1) {
                        $mt=date ('ymd-Hi').'+'.$Ledger->jr_id;
                        $uniq= $_ENV['TMP']. DIRECTORY_SEPARATOR."$mt-e-invoice.xml";
                        file_put_contents($uniq, $xml);
                        chmod ($uniq,"0774");
                        echo \Noalyss\Dbg::echo_file("file save $uniq");

                    }
                    // FOR BELGIUM : XML and PDF will be stored separately
                    // save XML string into the DB
                    $oid=$cn->lo_write($xml);
                    echo \Noalyss\Dbg::echo_var(1, "oid is $oid");
                    if ($oid == false) {
                        throw new Exception ('CV177 : cannot import e-invoice');
                    }
                    if ( $g_parameter->MY_INVOICE_FORMAT == 'UBL21BEL')
                    {
                        $acc_document->update_document_xml($oid);
                        $receipt= HtmlInput::show_receipt_document($Ledger->jr_id,$acc_document->d_filename)
                            . $acc_document->link_download_xml();
                    }elseif ($g_parameter->MY_INVOICE_FORMAT=='FACTURXFR')
                    {
                        $acc_document->replace_receipt($oid);
                        $receipt= HtmlInput::show_receipt_document($Ledger->jr_id,$acc_document->d_filename);
                    }

                }

            }
            
                
        }
        catch (\Exception $e) {
                if ( $e->getCode()==EXC_BALANCE)
                    echo_warning(_("enregistrement annulé: balance , voyer le fichier log"));
                else
                    echo_warning($e->getMessage());
                return;
        }


        /* Save the predefined operation */
        if ( isset($_POST['opd_name']) && trim($_POST['opd_name']) != "" )
        {
            $opd=new Pre_operation($cn);
            $opd->get_post();
            $opd->save();
        }

        /* Show button  */
        echo '<h1>'._("Enregistré").'</h1>';
        if ($flag_invoice == 1) {
            echo_warning(_("Impossible de générer facture électronique") );
            $xmldocument->display_error();
        }
        echo $Ledger->confirm($_POST,true);
        /* Show link for Invoice */
        if ($receipt != "")
        {
            echo '<h2 class="h-section">'._('Document').' </h2>';
            echo $receipt;
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
else if ( isset($_REQUEST ['p_jrn']) ) {
    $Ledger->id=$http->request('p_jrn','number');
}
else if (isset($_REQUEST['p_jrn_predef']))
{
    $Ledger->id=$http->request('p_jrn_predef','number');
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
        $action_id=$http->get('ag_id',"number");
        $cp=$http->get('cp','number',0);
        $array=$Ledger->convert_from_follow($action_id,$cp);
        echo HtmlInput::hidden("ledger_type", "VEN");
        echo HtmlInput::hidden("ac", $http->get('ac'));
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
        echo HtmlInput::hidden("ledger_type", "VEN");
        echo HtmlInput::hidden("ac", $strac);
        echo HtmlInput::hidden("sa", "p");
        $action_id=$http->get('ag_id',"string","");
        echo HtmlInput::hidden("action_gestion",$action_id);
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
		echo '<script> update_receipt()</script>';
    }
$e_date=$http->request("e_date","string","");

if ($e_date=="" && $g_parameter->MY_DATE_SUGGEST=='Y')
{
    echo create_script(" get_last_date()");
}

       
echo create_script(" update_name()");	
return;
?>
