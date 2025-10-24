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
 * \brief answer to the ajax request for the ledger
 * it means :
    - detail of an operation (expert, user and analytic view)
    - removal of an operation
    - load a receipt document
    - for reconcialiation
    - update of analytic content
* 
*/
if (!defined('ALLOWED')) die(_('Non authorisé'));

$http = new HttpInput();

/**
 * Check if we receive the needed data (jr_id...)
 */
global $g_user, $cn, $g_parameter;
mb_internal_encoding("UTF-8");

try {
    $action = $http->request('act');
    $jr_id = $http->request('jr_id');
    $div = $http->request('div');        /* the div source and target for javascript */
    $gDossier = dossier::id();

} catch (Exception $exc) {
    record_log($exc);
    return;
}

/**
 *if $_SESSION[SESSION_KEY.'g_user'] is not set : echo a warning
 */

$cn = Dossier::connect();
$g_parameter = new Noalyss_Parameter_Folder($cn);

$g_user->check();
if ($g_user->check_dossier(dossier::id(), true) == 'X') {
    ob_start();
    require_once NOALYSS_TEMPLATE . '/ledger_detail_forbidden.php';
    echo HtmlInput::button_close($div);
    $html = ob_get_contents();
    ob_end_clean();
    $html = escape_xml($html);
    header('Content-type: text/xml; charset=UTF-8');
    echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl>$div</ctl>
<code>$html</code>
</data>
EOF;
    exit();
}


// check if the user can access the ledger where the operation is (view) and
// if he can modify it
$op = new Acc_Operation($cn);
$op->jr_id = $jr_id;
$ledger = $op->get_ledger();
if ($ledger == "") {

    ob_start();
    echo HtmlInput::title_box(_("Information"), $div);
    require_once NOALYSS_TEMPLATE . '/ledger_detail_forbidden.php';
    echo HtmlInput::button_close($div);
    $html = ob_get_contents();
    ob_end_clean();

    $html = escape_xml($html);
    if (!headers_sent()) {
        header('Content-type: text/xml; charset=UTF-8');
    }
    echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl>$div</ctl>
<code>$html</code>
</data>
EOF;
    exit();

}
$access = $g_user->get_ledger_access($ledger);
if ($access == 'X') {
    ob_start();
    echo HtmlInput::title_box(_("Information"), $div);
    require_once NOALYSS_TEMPLATE . '/ledger_detail_forbidden.php';
    echo HtmlInput::button_close($div);
    $html = ob_get_contents();
    ob_end_clean();
    $html = escape_xml($html);
    header('Content-type: text/xml; charset=UTF-8');
    echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl>$div</ctl>
<code>$html</code>
</data>
EOF;
    exit();
}
$html = var_export($_REQUEST, true);
switch ($action) {
    ///////////////////////////////////////////////////////////////////////////
    //  remove op
    ///////////////////////////////////////////////////////////////////////////
    case 'rmop':
        if ($access == 'W' && $g_user->check_action(RMOPER) == 1 && $g_parameter->MY_STRICT=='N') {
            ob_start();
            /* get the ledger */
            try {
                $cn->start();
                $oLedger = new Acc_Ledger($cn, $ledger);
                $oLedger->jr_id = $jr_id = $http->request('jr_id', "number");
                $oLedger->delete();
                $cn->commit();
                echo _("Opération Effacée");
            } catch (Exception $e) {
                record_log($e);
                $e->getMessage();
                $cn->rollback();
            }
            $html = ob_get_contents();
            ob_end_clean();
        } else {
            $html = _("Effacement refusé");
        }
        break;
    //////////////////////////////////////////////////////////////////////
    // DE Detail
    //////////////////////////////////////////////////////////////////////
    case 'de':
        ob_start();

        try {
            /* get detail op (D/C) */
            $op->get();
            /* return an obj. ACH / FIN or VEN or null if nothing is found*/
            $obj = $op->get_quant();

            $oLedger = new Acc_Ledger($cn, $ledger);
            if ($obj == null || $obj->signature == 'ODS') {
                /* only the details */
                require_once NOALYSS_TEMPLATE . '/ledger_detail_misc.php';
            } elseif ($obj->signature == 'ACH') {
                require_once NOALYSS_TEMPLATE . '/ledger_detail_ach.php';
            } elseif ($obj->signature == 'FIN') {
                require_once NOALYSS_TEMPLATE . '/ledger_detail_fin.php';
            } elseif ($obj->signature == 'VEN') {
                require_once NOALYSS_TEMPLATE . '/ledger_detail_ven.php';
            }
        } catch (Exception $e) {
            record_log($e);
            echo Icon_Action::close($div);
            echo '<h2 class="error">' . _("Désolé il y a une erreur") . '</h2>';
        }
        $html = ob_get_contents();
        ob_end_clean();

        break;
    /////////////////////////////////////////////////////////////////////////////
    // form for the file
    /////////////////////////////////////////////////////////////////////////////
    case 'file':
        $op->get();
        $obj = $op->get_quant();    /* return an obj. ACH / FIN or VEN or null if nothing is found*/

        $repo = new Database();
        html_min_page_start($_SESSION[SESSION_KEY . 'g_theme']);

        // if there is a receipt document
        if ($obj->det->jr_pj_name == '') {
            if (!isset($_REQUEST['ajax'])) {
                echo '<div class="op_detail_frame">';
            } else {
                echo "<div>";

            }
            if ($access == 'W') {
                $check_receipt = sprintf("check_receipt_size('%s','file%s')",
                    MAX_FILE_SIZE, $div);
                echo '<FORM METHOD="POST" ENCTYPE="multipart/form-data" id="form_file" >';

                $sp = new ISpan('file' . $div);
                $sp->style = "display:none;background-color:red;color:white;font-size:12px";
                $sp->value = _("Chargement");
                echo $sp->input();
                echo HtmlInput::hidden('act', 'loadfile');
                echo dossier::hidden();
                echo HtmlInput::hidden('jr_id', $jr_id);
                echo HtmlInput::hidden('div', $div);
                echo '<INPUT TYPE="FILE" id="receipt_id" name="pj" onchange="' . $check_receipt . '">';

                echo '<p id="receipt_info_id" style="display:inline"></p>';

                echo '</FORM>';
            } else {
                if (!isset($_REQUEST['ajax'])) {
                    echo '<div class="op_detail_frame">';
                } else {
                    echo "<div>";
                }


                echo _('Aucun fichier');
            }
            echo '</div>';
            exit();
        } else {
            // There is no document attached to this writing
            //
            if (!isset($_REQUEST['ajax'])) {
                echo '<div class="op_detail_frame">';
            } else {
                echo "<div>";

            }
            echo '<div class="op_detail_frame">';
            $x = '';
            if ($access == 'W' && $g_user->check_action(RMRECEIPT) == 1) {
                // Not possible to remove the file thanks a modal dialog box,
                // because of the frameset

                $x = Icon_Action::trash(uniqid(),
                    sprintf("if (confirm(content[47])) {document.location.href='ajax_misc.php?op=ledger&gDossier=%d&div=%s&jr_id=%s&act=rmf'}",
                        $gDossier, $div, $jr_id));

            }
            $filename = $obj->det->jr_pj_name;
            if (strlen($obj->det->jr_pj_name) > 60) {
                $filename = mb_substr($obj->det->jr_pj_name, 0, 60);
            }
            echo HtmlInput::show_receipt_document($jr_id, h($filename));
            // if using the XML Belgian format, add a tab for showing it
            $acc_document=new Acc_Document($cn,$jr_id);
            echo '<span style="margin-left:5rem">'.
                 $acc_document->link_download_xml()
                .'</span>';
            echo $x;
            echo '<p id="receipt_info_id" style="display:inline" ></p>';
            echo '</div>';
            echo '</body></html>';
            exit();
        }
/////////////////////////////////////////////////////////////////////////////
// load a file
/////////////////////////////////////////////////////////////////////////////
    case 'loadfile':
        if ($access == 'W' && isset ($_FILES)) {
            $cn->start();
            $acc_document=new \Acc_Document($cn,$jr_id);
            $acc_document->save_receipt();
            $cn->commit();
            // Show a link to the new file
            $op->get();
            $obj = $op->get_quant();    /* return an obj. ACH / FIN or VEN or null if nothing is found*/
            html_min_page_start($_SESSION[SESSION_KEY . 'g_theme']);
            if (!isset($_REQUEST['ajax'])) echo "<body class=\"op_detail_frame\">"; else echo "<body>";
            echo '<div class="op_detail_frame">';
            $x = "";
            // check if the user can remove a document
            if ($g_user->check_action(RMRECEIPT) == 1) {
                // Not possible to remove the file thanks a modal dialog box,
                // because of the frameset
                $x = Icon_Action::trash(uniqid(),
                    sprintf("if (confirm(content[47])) {document.location.href='ajax_misc.php?op=ledger&gDossier=%d&div=%s&jr_id=%s&act=rmf'}",
                        $gDossier, $div, $jr_id));
            }
            $filename = $obj->det->jr_pj_name;
            echo HtmlInput::show_receipt_document($jr_id, h($filename));
            echo $x;
            // if using the XML Belgian format, add a tab for showing it
            $acc_document=new Acc_Document($cn,$jr_id);
            echo '<span style="margin-left:5rem">'.
                 $acc_document->link_download_xml()
                .'</span>';
            echo '<p id="receipt_info_id" style="display:inline" ></p>';
            echo '</div>';
            echo '</div>';
            echo '</body></html>';
        }
        exit();
/////////////////////////////////////////////////////////////////////////////
// remove a file
/////////////////////////////////////////////////////////////////////////////
    case 'rmf':
        if ($access == 'W' && $g_user->check_action(RMRECEIPT) == 1) {
            $repo = new Database();
            html_min_page_start($_SESSION[SESSION_KEY . 'g_theme']);
            echo '<div class="op_detail_frame">';
            $check_receipt = sprintf("check_receipt_size('%s','file%s')",
                MAX_FILE_SIZE, $div);
            echo '<FORM METHOD="POST" ENCTYPE="multipart/form-data" id="form_file">';
            $sp = new ISpan('file' . $div);
            $sp->style = "display:none;width:155px;height:15px;background-color:red;color:white;font-size:10px";
            $sp->value = _("Chargement");
            echo $sp->input();

            echo HtmlInput::hidden('act', 'loadfile');
            echo dossier::hidden();
            echo HtmlInput::hidden('jr_id', $jr_id);
            echo HtmlInput::hidden('div', $div);

            echo '<INPUT TYPE="FILE" id="receipt_id" name="pj" onchange="' . $check_receipt . '">';
            echo '<p id="receipt_info_id" style="display:inline"></p>';
            echo '</FORM>';
            $ret = $cn->exec_sql("select jr_pj from jrn where jr_id=$1", array($jr_id));
            if (Database::num_row($ret) != 0) {
                $r = Database::fetch_array($ret, 0);
                $old_oid = $r['jr_pj'];
                if (strlen($old_oid) != 0) {
                    // check if this pj is used somewhere else
                    $c = $cn->get_value("select count(*) from jrn where jr_pj=$1",
                            [$old_oid]);
                    
                    if ($c == 1)
                        $cn->lo_unlink($old_oid);
                }
                
                $cn->exec_sql("update jrn set jr_pj=null, jr_pj_name=null, " .
                    "jr_pj_type=null  where jr_id=$1", array($jr_id));
                
                if ( ($oid_xml = $cn->get_value("select jr_document_xml from jrn where jr_id = $1",[$jr_id])) != "") 
                {
                    $cn->exec_sql("update jrn set jr_document_xml=null   where jr_id=$1", array($jr_id));
                    $cn->lo_unlink($oid_xml);
                }
            }
        }
        echo '</div>';
        exit();
/////////////////////////////////////////////////////////////////////////////
// Save operation detail
/////////////////////////////////////////////////////////////////////////////
    case 'save':
        ob_start();
        $http = new HttpInput();
        try {
            $cn->start();
            if ($access == "W" ) {
                if (isset($_POST['p_ech'])) {
                    $ech = $http->post('p_ech');
                    if (trim($ech) != '' && isDate($ech) != null) {
                        $cn->exec_sql("update jrn set jr_ech=to_date($1,'DD.MM.YYYY') where jr_id=$2",
                            array($ech, $jr_id));

                    } else {
                        $cn->exec_sql("update jrn set jr_ech=null where jr_id=$1",
                            array($jr_id));

                    }
                }

                if (isset($_POST['p_date_paid'])) {
                    $ech = $http->post('p_date_paid');
                    if (trim($ech) != '' && isDate($ech) != null) {
                        $cn->exec_sql("update jrn set jr_date_paid=to_date($1,'DD.MM.YYYY') where jr_id=$2",
                            array($ech, $jr_id));

                    } else {
                        $cn->exec_sql("update jrn set jr_date_paid=null where jr_id=$1",
                            array($jr_id));

                    }
                }
                $oLedger=new Acc_Ledger($cn,$ledger);
                $npj=$http->post('npj');
                // protect receipt number
                if ( ($g_parameter->MY_PJ_SUGGEST == 'A'||$g_user->check_action(UPDRECEIPT)==0)  && $oLedger->get_type() !='FIN') {
                    $npj=$cn->get_value("select jr_pj_number from jrn where jr_id=$1",[$jr_id]);
                }
                // protect date in strict mode
                $date=$http->post("p_date");
                if (  $g_parameter->MY_STRICT=='Y' && $g_user->check_action(UPDDATE)==0) {
                    $date=$cn->get_value("select to_char(jr_date,'DD.MM.YYYY') from jrn where jr_id=$1",[$jr_id]);
                }
                $cn->exec_sql("update jrn set jr_comment=$1,jr_pj_number=$2,jr_date=to_date($4,'DD.MM.YYYY'),jr_optype=$5 where jr_id=$3",
                    array($http->post('lib'), $npj, $jr_id,$date, $http->post('jr_optype')));
                $find_periode = $cn->get_value("select comptaproc.find_periode($1)",[$date]);

                $cn->exec_sql("update jrnx set j_date=to_date($1,'DD.MM.YYYY'),j_tech_per=$3 where j_grpt in (select jr_grpt_id from jrn where jr_id=$2)",
                    array($date, $jr_id,$find_periode));
                $cn->exec_sql('update operation_analytique set oa_date=j_date from jrnx
				where
				operation_analytique.j_id=jrnx.j_id  and
				operation_analytique.j_id in (select j_id
						from jrnx join jrn on (j_grpt=jr_grpt_id)
						where jr_id=$1)
						', array($jr_id));
                
                $rapt = $http->post('rapt');

                if ($g_parameter->MY_UPDLAB == 'Y' && isset ($_POST['j_id'])) {
                    $a_rowid = $http->post("j_id");
                    for ($e = 0; $e < count($a_rowid); $e++) {
                        $id = "e_march" . $a_rowid[$e] . "_label";
                        $cn->exec_sql('update jrnx set j_text=$1 where j_id=$2', array($http->post($id), $a_rowid[$e]));
                    }
                }
                if (trim($rapt) != '') {
                    $rec = new Acc_Reconciliation ($cn);
                    $rec->set_jr_id($jr_id);

                    if (strpos($rapt, ",") != 0) {
                        $aRapt = explode(',', $rapt);
                        /* reconcialition */
                        foreach ($aRapt as $rRapt) {
                            if (isNumber($rRapt) == 1) {
                                // Add a "concerned operation to bound these op.together
                                $rec->insert($rRapt);
                            }
                        }
                    } else
                        if (isNumber($rapt) == 1) {
                            $rec->insert($rapt);
                        }
                }
                if (isset($_POST['ipaid'])) {
                    $cn->exec_sql("update jrn set jr_rapt='paid' where jr_id=$1", array($jr_id));
                } else {
                    $cn->exec_sql("update jrn set jr_rapt=null where jr_id=$1", array($jr_id));
                }
                ////////////////////////////////////////////////////
                // CA
                //////////////////////////////////////////////////
                $owner = new Noalyss_Parameter_Folder($cn);
                if ($owner->MY_ANALYTIC != "nu" && isset ($_POST['op'])) {
                    // for each item, insert into operation_analytique */
                    $opanc = new Anc_Operation($cn);
                    $opanc->save_update_form($_POST);
                }
                //////////////////////////////////////////////////////////////////
                //Save other info
                //////////////////////////////////////////////////////////////////
                $op->save_info($http->post('OTHER'), 'OTHER');
                $op->save_info($http->post('BON_COMMANDE'), 'BON_COMMANDE');

                ///////////////////////////////////////////////////////////////////
                // Save related
                //////////////////////////////////////////////////////////////////
                $related = $http->post("related", "string");
                if ($related == "0") {
                    throw new Exception('Parameter not send -> related' . __FILE__ . __LINE__, 10);
                }
                $op->insert_related_action($related);

            }
            echo 'OK';
            $cn->commit();
        } catch (Exception $e) {
            $html = ob_get_contents();
            ob_end_clean();
            record_log($e);
            record_log($html);

            if (DEBUGNOALYSS > 0) echo $e->getMessage();
            echo _("Changement impossible: on ne peut pas changer la date dans une période fermée");
            return;
        }
        $html = ob_get_contents();
        ob_end_clean();

        break;
    ////////////////////////////////////////////////////////////////////////////
    // remove a reconciliation
    ////////////////////////////////////////////////////////////////////////////
    case 'rmr':
        if ($access == 'W') {
            $rec = new Acc_Reconciliation($cn);
            $rec->set_jr_id($jr_id);
            $rec->remove($_GET['jr_id2']);
        }
        break;
    ////////////////////////////////////////////////////////////////////////////
    // ask for a date for reversing the operation
    ////////////////////////////////////////////////////////////////////////////
    case 'ask_extdate':
        $date = new IDate('p_date');
        $html .= "<form id=\"form_" . $div . "\" onsubmit=\"return reverseOperation(this);\">";
        $html .= HtmlInput::hidden('jr_id', $http->request('jr_id','number')) .
            HtmlInput::hidden('div', $div) .
            dossier::hidden() .
            HtmlInput::hidden('act', 'reverseop');

        $html .= '<h2 class="info">' . _('entrez une date') . ' </H2>' . $date->input();
        $html .= HtmlInput::submit('x', 'accepter');
        $html .= HtmlInput::button_close($div);
        $html .= '</form>';
        break;
    ////////////////////////////////////////////////////////////////////////////
    // Reverse an operation
    ////////////////////////////////////////////////////////////////////////////
    case 'reverseop':
        if ($access == 'W') {
            ob_start();
            try {
                $ext_date = $http->request("ext_date", "date");
                $ext_label = $http->request("ext_label");
                $cn->start();
                $oLedger = new Acc_Ledger($cn, $ledger);
                $oLedger->jr_id = $jr_id;
                if (trim($ext_label) == "") {
                    $ext_label = _("Extourne") . $cn->get_value("select jr_comment from jrn where jr_id=$1", [$jr_id]);
                }
                $oLedger->reverse($ext_date, $ext_label);
                $cn->commit();
                echo _("Opération extournée");
            } catch (Exception $e) {
                record_log($e);
                echo $e->getMessage();
                $cn->rollback();
            }
        }
        $html = ob_get_contents();
        ob_end_clean();
        break;

    case 'duplicateop':
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Duplicate operation
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////
        $operation = new Acc_Operation($cn);
        $operation->jr_id = $jr_id;
        ob_start();
        echo HtmlInput::title_box(_("Dupliquer une opération"), $div);
        echo $operation->form_clone_operation("cloneit");

        $html = ob_get_contents();
        ob_end_clean();


        break;
//------------------------------------------------
// Display note
//------------------------------------------------
    case 'note_input':
        $acc_operation_note= Acc_Operation_Note::build_jrn_id($jr_id);
        try {
            $ctl="box_input_note".$div;
            $html=HtmlInput::title_box(_("Note"), $ctl);
            $textarea= Acc_Operation_Note::build_textarea($div);
            if ( $http->get('tinymce')=='true')
                $textarea->value=$acc_operation_note->getJrnNoteSql()->get("n_html");
            else {
                $textarea->set_enrichText('plain');
                $textarea->value=$acc_operation_note->getJrnNoteSql()->get("n_text");
            }
            
            // start FORM
            $html.=sprintf('<form id="form_input_note%s" onsubmit="noalyss.save_note(this);return false;">',
                    $div);
            
            $html.=$textarea->input();
            
            $html.=HtmlInput::hidden('gDossier', $gDossier);
            $html.=HtmlInput::hidden('div', $div);
            $html.=HtmlInput::hidden('jr_id', $jr_id);
            $html.=HtmlInput::hidden('act', 'note_save');
            $html.=HtmlInput::submit("save", _("Sauve"));
            $html.=HtmlInput::hidden("input_html", $textarea->id);
            $html.=HtmlInput::button_close($ctl);
            // end form
            $html.="</form>";
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        
        break;
    case 'note_save':
//------------------------------------------------
// save note
//------------------------------------------------

        /**
         * received parameters 
         *      - gDossier
         *      - div
         *      - jr_id
         *      - note_html
         */
         $ctl="box_input_note".$div;
         $acc_operation_note= Acc_Operation_Note::build_jrn_id($jr_id);
         $acc_operation_note->setOperation_id($jr_id);
         $acc_operation_note->setNote($http->post("note_html",'raw'));
         $acc_operation_note->save();
         $html=$http->post("note_html","raw");
        break;
    case 'note_refresh':
        $acc_operation_note= Acc_Operation_Note::build_jrn_id($jr_id);
        echo substr($acc_operation_note->getNote(),0,120);
        
        return;
}
$html = escape_xml($html);
if (!headers_sent()) {
    header('Content-type: text/xml; charset=UTF-8');
}


echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl>$div</ctl>
<code>$html</code>
</data>
EOF;
