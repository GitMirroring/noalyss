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
 *
 *
 * \brief to write into the ledgers ODS a new operation
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
\Noalyss\Dbg::echo_file(__FILE__);


global $g_user,$g_parameter,$http;
$cn=Dossier::connect();

$id_predef = $http->request('p_jrn_predef','number',-1);
$id_ledger = $http->request('p_jrn','number',$id_predef);

$ledger = new Acc_Ledger($cn, $id_ledger);
$first_ledger=$ledger->get_first('ODS');
$ledger->id = ($ledger->id == -1) ? $first_ledger['jrn_def_id'] : $id_ledger;

// check if we can write in the ledger
if ( $g_user->check_jrn($ledger->id)=='X')
{
	alert(_("Vous ne pouvez pas écrire dans ce journal, contactez votre administrateur"));
        echo '<span class="warning">'.
              _("Vous ne pouvez pas écrire dans ce journal, contactez votre administrateur").
              '</span>';
	return;
}              
echo '<div class="content">';


echo '<div id="jrn_name_div">';
echo '<h1 id="jrn_name" style="display:inline">' . $ledger->get_name() . '</h1>';
echo '</div>';

// Show the predef operation
// Don't forget the p_jrn
$p_post=$_POST;
if ( isset ($_GET['action']) && ! isset($_POST['correct']) && ! isset($correct) )
{
	if ( $_GET['action']=='use_opd')
	{
            // get data from predef. operation
            $op=new Pre_operation($cn);
            $p_post=null;
            if ( isset($_REQUEST['pre_def']) && $_REQUEST['pre_def'] != '')
            {
                $op->set_od_id($http->request('pre_def','number'));
                $p_post=$op->compute_array();
                // operation description are not in the same variable
                $p_post['desc']=$p_post['e_comm'];
            }
	}
}
$p_msg=(isset($p_msg))?$p_msg:"";
if ( !empty ($p_msg))
{
    print '<span class="warning">'.$p_msg.'</span>';
}
echo '<form method="post"  class="print" onsubmit="return controleBalance();" >';
echo dossier::hidden();
echo HtmlInput::request_to_hidden(array('ac','jr_optype'));

$default_currency=new Acc_Currency($cn,0);

echo $ledger->input($p_post);

$style=' style="display:inline-block;width: 15rem;text-align: right"';
?>
<div style="position:absolute;width:40%;right:20px">
    <table class="info_op">
        <tr>
            <td style="border:1px solid "><?=_('Totaux')?></td>
            <td style="border:1px solid lightgrey"><?= _('Débit') ?>
            <span id="totalDeb" <?=$style?>></span>
            </td>
            <td style="border:1px solid lightgrey"><?= _('Crédit') ?>
            <span id="totalCred"  <?=$style?>></span>
            </td>
            <td style="border:1px solid lightgrey"><?= _('Difference') ?>
            <span id="totalDiff"   <?=$style?>></span>
            </td>
        </tr>
        <?php // For currency ?>
        <tr id="row_currency">
            <td style="border:1px solid "><?=$default_currency->get_code()?></td>

            <td style="border:1px solid lightgrey"><?= _('Débit') ?>
            <span id="default_currency_deb"  <?=$style?>></span>
            </td>

            <td style="border:1px solid lightgrey"><?= _('Crédit') ?>
            <span id="default_currency_cred"   <?=$style?>></span>
            </td>

            <td></td>
        </tr>
    </table>

</div>

<?php

$iconcerned=new IConcerned('jrn_concerned');
$iconcerned->amount_id="totalDeb";
printf (_("Opération rapprochée : %s"),$iconcerned->input());

echo '<p>';
echo Html_Input_Noalyss::ledger_add_item("M");
echo HtmlInput::submit('summary', _('Sauvez'));
echo '</p>';

echo '</form>';

echo "<script>checkTotalDirect();</script>";
echo create_script(" update_name()");

$e_date=$http->request("e_date","string","");

if ($e_date=="" && $g_parameter->MY_DATE_SUGGEST=='Y')  {
    echo create_script(" get_last_date()");
  }

echo '</div>';

?>
