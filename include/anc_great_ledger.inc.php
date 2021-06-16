<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
/**
 *@file
 *@brief Print the great ledger for Analytic accounting
 * @see Anc_GrandLivre
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
echo '<div style="content">';
global $http;
require_once NOALYSS_INCLUDE.'/class/anc_grandlivre.class.php';
$cn=Dossier::connect();
$grandLivre=new Anc_Grandlivre($cn);

$grandLivre->get_request();

/*
 * Form
 */
echo '<form method="get" >';
echo $grandLivre->display_form();
echo '<p>' . HtmlInput::submit('Recherche', _('Rechercher')) . '</p>';
echo HtmlInput::request_to_hidden(array('sa','ac','gDossier'));
echo '</form>';

$result=$http->request('result',"string",null);

if ($result != null)
{
    $grandLivre->load();
    if ($grandLivre->has_data != 0 )
    {
        echo '<span style="display:block">';
          echo _('Tout sélectionner')." ".ICheckBox::toggle_checkbox('export_pdf_bt1','export_anc_receipt_pdf');
        echo '</span>';
        $task_id=uniqid();
        echo $grandLivre->button_export_csv();
        printf ('<form method="GET" id="export_anc_receipt_pdf" action="export.php" 
            style="display:inline" onsubmit="return start_export_anc_receipt_pdf(\'%s\',\'%s\');">',
            $task_id,
            _("Le traitement est en cours ,  merci de patienter sans recharger la page")
            );
        echo HtmlInput::hidden("task_id",$task_id);
        $type_pdf=new Select_Box("type_pdf",_("Type export PDF"));
        $type_pdf->add_value(_("Un seul PDF"),1);
        $type_pdf->add_value(_("Un PDF par opération"),2);
        $type_pdf->set_position("in-absolute");
        echo $type_pdf->input();
        
        // propose to download also the reconcilied operation with its receipt
//        echo _("Avec documents des opérations rapprochées");
//        $checkbox=new ICheckBox("receipt_reconcilied", 0);
//        
//        echo $checkbox->input();
        $type_pdf=new Select_Box("reconcilied_document",_("Opérations rapprochées"));
        $type_pdf->add_value(_("Opérations rapprochées avec documents"),1);
        $type_pdf->add_value(_("Opérations rapprochées sans documents"),2);
        $type_pdf->set_position("in-absolute");
        echo $type_pdf->input();

        echo $grandLivre->button_export_pdf();
        echo $grandLivre->display_html();
        echo HtmlInput::get_to_hidden(array('ac','gDossier','sa'));
        echo $grandLivre->button_export_pdf();
        echo '</form>';
        echo $grandLivre->button_export_csv();
        ?>
<script>
    function start_export_anc_receipt_pdf(p_task_id,p_message)
    {
        var a=document.getElementsByName("ck[]");
        var i=0;
        var valid=false;
        for ( i =0;i < a.length;i++) {
            if ( a[i].checked == true) {
                valid=true;
                break;
            }
        }
        if (document.getElementById("type_pdf").value == "-1" )
        {
            valid=false;
        }
         if (document.getElementById("reconcilied_document").value == "-1" )
        {
            valid=false;
        }
        if ( valid  ) {
            progress_bar_start(p_task_id,p_message);
            return true;
        } else {
            smoke.alert("<?=_('Choisissez au moins une opération, les opérations rapprochées et le type d\'export')?>");
            return false;
        }
        
    }

</script>
<?php
    }
    else
    {
        echo '<p class="notice">';
        echo _('Aucune donnée trouvée');
        echo '</p>';
    }
    
}
echo '</div>';
?>
