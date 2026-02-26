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
 *
 *
 * \brief reconcile operation
 *
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
global $g_failed,$g_succeed,$http;
bcscale(2);
?>
<script>
    var Bank_Reconciliation =
    {
    /**
    * For each checkbox , add an event on click
    * @param {string} p_range_name name of the checkbox object
    * @returns {undefined}
    */
    activate_checkbox_range:function (p_range_name) {
            let node_lstCheckBox = document.getElementsByClassName(p_range_name);
            var aCheckBox=Array.from(node_lstCheckBox)
            if (aCheckBox == undefined) {
                console.error("Bank_Reconciliation.activate_checkbox_range_failed")
            }

            aCheckBox.forEach(elt => elt.addEventListener ('click',function (event) {
                    Bank_Reconciliation.checkbox_set_range(event, elt, p_range_name);
                },false));
        },
        checkbox_set_range:function (evt, elt, p_name) {
        if (!evt.shiftKey) {
            lastcheck = elt;
            return;
        }
        var aName = document.getElementsByClassName(p_name);

        var from = 0;
        var end = 0;
        for (var i = 0; i < aName.length; i++) {
            if (aName[i] == elt) {
                endcheck = aName[i];
                from = i;
            }
            if (aName[i] == lastcheck) {
                end = i;
            }
        }
        if (from > end) {
            let a = from;
            from = end;
            end = a;
        }
        var check = (aName[from].checked) ? true : false;
        for (x = from; x <= end; x++) {
            if ( window.getComputedStyle(aName[x].parentNode.parentNode).display == "none" ) { continue; }
            aName[x].checked = check;
            if( x < end && x > from   ) {
                Bank_Reconciliation.update_selected(aName[x],aName[x].getAttribute('amount_operation'));
                Bank_Reconciliation.update_remain(aName[x],aName[x].getAttribute('amount_operation'));
            }
        }
    },


    update_selected:function (p_node,p_amount) {
        
        p_amount=parseFloat(p_amount);
        try {
            if (p_node.checked ) 
            {
               var selected=parseFloat($('selected_amount').innerHTML)+p_amount;
               $('selected_amount').innerHTML=Math.round(selected*100)/100;
            } else {
               var selected=parseFloat($('selected_amount').innerHTML)-p_amount;
               $('selected_amount').innerHTML=Math.round(selected*100)/100;

            }
        } catch(e) {
            if (console) {console.error('Bank_Reconciliation.update_selected :'+e.message);}
        }
    },
    update_remain:function (p_node,p_amount) {
         p_amount=parseFloat(p_amount);
    try {
            if ( parseFloat($('delta_amount').innerHTML) == 0) return;
            if (p_node.checked ) 
            {
               var selected=parseFloat($('remain_amount').innerHTML)-p_amount;
               $('remain_amount').innerHTML=Math.round(selected*100)/100;
            } else {
               var selected=parseFloat($('remain_amount').innerHTML)+p_amount;
               $('remain_amount').innerHTML=Math.round(selected*100)/100;

            }        
        } catch(e) {
            if (console) {console.error('Bank_Reconciliation.update_remain :'+e.message);}

        }   
    },
    update_delta:function () {
        try {
            var delta=parseFloat($('end_extrait').value)-parseFloat($('start_extrait').value);
            delta=Math.round(delta*100)/100;
            $('delta_amount').innerHTML=delta;
            var remain=delta-parseFloat($('selected_amount').innerHTML);
            $('remain_amount').innerHTML=Math.round(remain*100)/100;
        } catch(e) {
            if (console) {console.error('Bank_Reconciliation.update_delta :' +e.message);}
        }
    },
    recompute:function (p_form) {
        try {
            var form=$(p_form);
            var i=0;
            for (i=0;i<form.length;i++) {
                var e=form.elements[i];
                if (e.type=='checkbox' &&  window.getComputedStyle(e.parentNode.parentNode).display != "none" ) 
                {
                        e.click();
                }
            }
            remove_waiting_box();
        } catch (e) {
            if (console) {console.error('Bank_Reconciliation.recompute :' +e.message);}
        }
    }
    }
</script>
<?php
echo '<div class="content">';
$Ledger = new Acc_Ledger_Fin($cn, 0);
if (!isset($_REQUEST['p_jrn']))
{
	$a = $Ledger->get_first('fin');
	if ( isset($a['jrn_def_id'])) {
	    $Ledger->id = $a['jrn_def_id'];
    } else {
	    $Ledger->id=0;
    }
}
else
{
    $Ledger->id =$http->request("p_jrn","number",0);
}
$jrn_priv = ($Ledger->id == 0)?"X":$g_user->get_ledger_access($Ledger->id);
if (isset($_GET["p_jrn"]) && $jrn_priv == "X")
{
	NoAccess();
	return;
}
$end_extrait=$http->post("end_extrait", "string",0);
$start_extrait=$http->post("start_extrait","string", 0);
if ( isNumber($end_extrait) == 0 )
{
    echo '<span class="notice">';
    echo _('Donnée invalide');
    echo '</span>';
    $end_extrait=0;
}
if ( isNumber($start_extrait) == 0 )
{
    echo '<span class="notice">';
    echo _('Donnée invalide');
    echo '</span>';
    $start_extrait=0;
}
//-------------------------
// save
//-------------------------
if (isset($_POST['save']))
{
    $receipt_nb=$http->post("ext");
    if (trim($receipt_nb) == '' && isset($_POST['op'])) {
        echo_warning(_('Numéro de relevé est obligatoire'));
    }
	if (trim($receipt_nb) != '' && isset($_POST['op']))
	{
		$array = $_POST['op'];
		$tot = 0;
		$cn->start();
		for ($i = 0; $i < count($array); $i++)
		{
			$cn->exec_sql('update jrn set jr_pj_number=$1 where jr_id=$2', array($receipt_nb, $array[$i]));
			$tot = bcadd($tot, $cn->get_value('select qf_amount from quant_fin where jr_id=$1', array($array[$i])));
		}
		$diff = bcsub($end_extrait, $start_extrait);
		if ($diff != 0 && $diff != $tot)
		{
			$remain=bcsub($tot,$diff);
			$cn->rollback();
                        $str_message=_("D'après l'extrait il y aurait du avoir un montant de $diff à rapprocher alors qu'il y a $tot rapprochés, mise à jour annulée, la différence est de $remain");
			alert($str_message);
			echo '<div class="error">';
			echo '<p>'.$g_failed.$str_message.'</p>';
                        /* if file : warning that file is not uploaded*/
                        echo    '<p>'.
                                _('Attention : Fichier non chargé et rapprochement annulé').
                                '</p>';
			echo '</div>';
		}
		else
		{
		    echo '<div class="content">'.$g_succeed._(' Mise à jour extrait')." ".$receipt_nb.'</div>';
                    // -- chargement fichier
                    $oid=null;
                    if ( $_FILES['file_receipt']['name'] != "" && $_FILES['file_receipt']['error'] == 0)
                    {
                        $oid=$cn->upload('file_receipt',true);
                        echo _("Fichier sauvé");
                    }
                    if( $_FILES['file_receipt']['error'] != 4 && $_FILES['file_receipt']['error'] !=0 )
                        echo_warning(_("Fichier non chargé: mise à jour annulée"));
                    else    
                    {
                        for ($i = 0; $i < count($array); $i++)
                        {
                              $cn->exec_sql("update jrn set jr_pj=$1 , jr_pj_name=$2,
                                jr_pj_type=$3  where jr_id=$4",
                                array($oid,$_FILES['file_receipt']['name'] ,$_FILES['file_receipt']['type'],$array[$i]));
                        }
                    }
		  }

		$cn->commit();
	}
}
//-------------------------
// show the operation of this ledger
// without receipt number
//-------------------------
echo '<div class="content">';
echo '<form method="get">';
echo HtmlInput::get_to_hidden(array('gDossier', 'ledger_type', 'ac', 'sa'));
$wLedger = $Ledger->select_ledger('FIN', 3,FALSE);
if ($wLedger == null)
	exit('Pas de journal disponible');
echo '<div id="jrn_name_div">';
echo '<h1 id="jrn_name" style="display:inline">' . $Ledger->get_name() . '</h1>';
echo '</div>';
$wLedger->javascript = "onchange='this.form.submit()';";
echo $wLedger->input();
echo HtmlInput::submit('ref', 'Rafraîchir');
echo '</form>';
echo '<span id="bkname" style="display:block">' . hb(h($Ledger->get_bank_name())) . '</span>';

echo '<form method="post" id="rec1"   enctype="multipart/form-data">';

echo dossier::hidden();
echo HtmlInput::get_to_hidden(array('sa', 'p_action', 'p_jrn'));

$operation = $cn->get_array("select jr_id,jr_internal,
								jr_comment,
								to_char(jr_date,'DD.MM.YYYY') as fmt_date,
								jr_montant,
								to_char(jr_date,'YYYYMMDD') as raw_date
                              from jrn where jr_def_id=$1 and (jr_pj_number is null or jr_pj_number='') order by jr_date", array($Ledger->id));

echo '<p>';
$iextrait = new IText('ext');
 $iextrait->value=$http->post('ext','string','');

$nstart_extrait = new INum('start_extrait');
$nstart_extrait->value=$start_extrait;
$nstart_extrait->javascript='onchange="format_number(this,2);Bank_Reconciliation.update_delta();"';

$nend_extrait = new INum('end_extrait');
$nend_extrait->value=$end_extrait;
$nend_extrait->javascript='onchange="format_number(this,2);Bank_Reconciliation.update_delta();"';

echo _("Extrait / relevé :") . $iextrait->input();
echo _('solde Début') . $nstart_extrait->input();
echo _('solde Fin') . $nend_extrait->input();
$take_end=new IButton('select_all');
$take_end->label=_('Reprendre le solde de fin');
$take_end->javascript="document.getElementById('start_extrait').value=document.getElementById('end_extrait').value;document.getElementById('end_extrait').focus()";
echo $take_end->input();
echo '</p>';
echo '<p>';
echo _('Cherche').Icon_Action::infobulle(25);
echo HtmlInput::filter_table("t_rec_bk", "0,1,2,3","1");
echo '</p>';
echo HtmlInput::submit('save', 'Mettre à jour le n° de relevé bancaire');
echo '<span style="display:block">';
	echo '</span>';
$select_all=new IButton('select_all');
$select_all->label=_('Inverser la sélection');
$select_all->javascript="Bank_Reconciliation.recompute('rec1')";
echo '<table id="t_rec_bk" class="sortable" style="width:90%;margin-left:5%">';

$r ='<th class=" sorttable_sorted">'.'Date '.Icon_Action::infobulle(17).'</th>';
$r.=th('Libellé');
$r.=th('N° interne');
$r.=th('Montant', ' style="text-align:right"');
$r.='<th class="sorttable_nosort"   style="text-align:center">'.$select_all->input().'</th>';
echo tr($r);

$tot_not_reconcilied = 0;
$diff = 0;
$delta=bcsub($end_extrait,$start_extrait);
$selected_amount=0;
$remain_amount=$delta;
for ($i = 0; $i < count($operation); $i++)
{
        $iradio = new ICheckBox('op[]');
        $iradio->set_range("operation_ck");
	$row = $operation[$i];
	$r = '';
	$js = HtmlInput::detail_op($row['jr_id'], $row['jr_internal']);
	$r.='<td sorttable_customkey="'.$row['raw_date'].'">'.$row['fmt_date'].'</td>';
	$r.=td($row['jr_comment']);
	$r.=td($js);
	$amount=$cn->get_value('select qf_amount from quant_fin where jr_id=$1', array($row['jr_id']));
	$r.='<td class="num sorttable_numeric"  sorttable_customkey="'.$amount.'" style="text-align:right">'.nbm ($amount).'</td>';

	$diff=bcadd($diff,$amount);
	$tot_not_reconcilied+=$row['jr_montant'];
	$iradio->value = $row['jr_id'];
	$iradio->selected=false;
        $iradio->set_attribute("amount_operation", $amount);
        $iradio->javascript=sprintf(' onchange = "Bank_Reconciliation.update_selected(this,%s);Bank_Reconciliation.update_remain(this,%s)"',$amount,$amount);
	if (isset($_POST['op']))
	{
            $a_operation=$http->post("op");
            $nb_operation=count($a_operation);
		for ($x=0;$x<$nb_operation;$x++)
		{
			if ($row['jr_id']==$a_operation[$x])
			{
				$iradio->selected=true;
                                $selected_amount+=$amount;
                                $remain_amount-=$amount;
				break;
			}
		}
	}
	$r.=td(HtmlInput::hidden('jrid['.$i.']', $row['jr_id']) . $iradio->input(), 'sorttable_customkey="1" style="text-align:center" ');
	if ($i % 2 == 0)
		echo tr($r, ' class="odd" ');
	else
		echo tr($r,' class="even" ');
}
echo '</table>';

$bk_card = new Fiche($cn);
$bk_card->id = $Ledger->get_bank();
$filter_year = "  j_tech_per in (select p_id from parm_periode where  p_exercice='" . $g_user->get_exercice() . "')";

/*  get saldo for not reconcilied operations  */
$saldo_not_reconcilied = $bk_card->get_solde_detail($filter_year . " and j_grpt in (select jr_grpt_id from jrn where trim(jr_pj_number) ='' or jr_pj_number is null)");

/*  get saldo for reconcilied operation  */
$saldo_reconcilied = $bk_card->get_solde_detail($filter_year . " and j_grpt in (select jr_grpt_id from jrn where trim(jr_pj_number) != '' and jr_pj_number is not null)");

/* solde compte */
$saldo = $bk_card->get_solde_detail($filter_year);
echo '<div style="float:right;margin-right:100px;font-size:120%;font-weight:bolder">';
echo '<table id="total_div_id">';
echo '
    <tr>
    <td>'._('Différence relevé').'</td>
    <td id="delta_amount" class="num" >'.$delta.'</td>
    </tr>
    <tr>
    <td>'._('Montant sélectionné').'</td>
    <td class="num"  id="selected_amount">'.$selected_amount.'</td>
    </tr>
    <tr>
    <td>'._('Reste à selectionner').'</td>
    <td class="num" id="remain_amount">'.$remain_amount.'</td>
    </tr>
    
';
echo '</table>';
echo '</div>';
echo '<table>';
echo '<tr>';
echo td("Solde compte  ");
echo td(nbm(bcsub($saldo['debit'] , $saldo['credit'])), ' style="text-align:right"');
echo '</tr>';

echo '<tr>';
echo td("Solde non rapproché ");
echo td(nbm (bcsub($saldo_not_reconcilied['debit'], $saldo_not_reconcilied['credit'])), ' style="text-align:right"');
echo '</tr>';

echo '<tr>';
echo td("Solde  rapproché ");
echo td(nbm(bcsub($saldo_reconcilied['debit'] , $saldo_reconcilied['credit'])), ' style="text-align:right"');
echo '</tr>';


echo '<tr>';
echo td("Total montant ");
echo td(nbm ($tot_not_reconcilied), ' style="text-align:right"');
echo '</tr>';

echo '</table>';

$receipt=new IFile('file_receipt');
$receipt->setAlertOnSize(true);
echo _("Pièce justificative"),"&nbsp;" ,
    $receipt->input();
echo '<p class="text-align:center">';
echo HtmlInput::submit('save', 'Mettre à jour le n° de relevé bancaire');
echo '</p>';
echo '</form>';
echo '</div>';
if ( count($operation) >0) { 
    echo <<<EOF
    <script>
        (function () {Bank_Reconciliation.activate_checkbox_range('operation_ck');})()
    </script>
EOF;    
}
return;
?>

