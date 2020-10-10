<?php
/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2002-2019) Author Dany De Bontridder <danydb@noalyss.eu>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief called from p_id already set, 
 * @see action_document_type_mtable::input
 */
// SQL Object Document_Type
$table=$this->get_table();
// db connx
$cn=$table->cn;
?>

<?php echo _('Prochain numéro') ?>
<p class="info" style="display:inline">
    (
<?php echo _('numéro actuel') ?>
<?php
$last=0;
if ( $table->dt_id > 0) {
    $ret=$cn->get_array("select last_value,is_called from seq_doc_type_".$table->dt_id);

    $last=$ret[0]['last_value'];
    /* !
     * \note  With PSQL sequence , the last_value column is 1 when before   AND after the first call, to make the difference between them
     * I have to check whether the sequence has been already called or not */
    if ($ret[0]['is_called']=='f')
        $last--;
}
echo $last;
?>
    )
</p>

<?php
echo
Icon_Action::infobulle(15);
?>
<?php
$seq=new INum('seq', 0);
echo $seq->input();
?>
<div>
    <h3 class="info" sytle="margin-block: 4px"><?php echo _("Détail") ?></h3>
    <ul class="tab_row" style="padding-top: 0px">
        <li>
<?php
$i=new ICheckBox("detail_operation",1);
if ( Document_Option::is_enable_operation_detail($table->dt_id)) $i->set_check(1);else $i->set_check(0);
echo $i->input();
echo _("Détail opération");
$select_detail_operation=new ISelect("select_option_operation");
$select_detail_operation->value=array(["value"=>"VEN","label"=>_("Prix vente")],
                                      ["value"=>"ACH","label"=>_("Prix achat")]);
$select_detail_operation->set_value(Document_Option::option_operation_detail($table->dt_id));
echo $select_detail_operation->input();
?>
        </li> 
        <li>
<?php
$i=new ICheckBox("det_contact_mul",1);
if ( Document_Option::is_enable_contact_multiple($table->dt_id)) $i->set_check(1); else $i->set_check(0);
echo $i->input();
echo _("Contacts multiples");
?>
        </li> 
        <li>
<?php
$i=new ICheckBox("make_invoice",1);
if ( Document_Option::is_enable_make_invoice($table->dt_id)) $i->set_check(1); else $i->set_check(0);
echo $i->input();
echo _("Création de facture");
?>
        </li> 
    </ul>

</div>
<div>
     <h3 class="info" sytle="margin-block: 4px"><?php echo _("Options contact") ?></h3>
    <ul class="tab_row" style="padding-top: 0px">
<?php        
            
$nb_option=count($aOption);
for ($i=0;$i<$nb_option;$i++)
{
    echo '<li>';
    
    echo HtmlInput::hidden("cor_id[]", $aOption[$i]["cor_id"]);
    $is=new InputSwitch("contact_option$i",$aOption[$i]["jdoc_enable"]);
    echo $is->input();
    echo "&nbsp;&nbsp;&nbsp;"._($aOption[$i]['cor_label']);
    echo '</li>';
}
?>
</ul>
</div>
