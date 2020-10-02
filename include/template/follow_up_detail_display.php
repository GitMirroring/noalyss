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
// Copyright (2002-2020) Author Dany De Bontridder <danydb@noalyss.eu>

/**
 * @file
 * @brief Called from Follow_Up_Detail for displaying detail
 * @see Follow_Up_Detail::display
 */
// Number of articles
$article_count=(count($p_follow_up->aAction_detail)==0)?MAX_ARTICLE:count($p_follow_up->aAction_detail);
// total item and vat
$tot_item=0;
$tot_vat=0;
$text=new IText();
$num=new INum();
$itva=new ITva_Popup();
$readonly=$p_view;

// default menu for invoice
$menu=new Default_Menu();
?>
<div id="follow_up_detail">
    <?php echo HtmlInput::hidden("nb_item", $article_count); ?>

    <table style="width:100%"  id="sold_item">
        <tr>
            <th><?php echo _('Fiche') ?></th>
            <th><?php echo _('Description') ?></th>
            <th><?php echo _('prix unitaire') ?></th>
            <th><?php echo _('quantité') ?></th>
            <th><?php echo _('Code TVA') ?></th>
            <th><?php echo _('Montant TVA') ?></th>
            <th><?php echo _('Montant TVAC') ?></th>

        </tr>
        <?php
        for ($i=0; $i<$article_count; $i++):
            /* fid = Icard  */
            $icard=new ICard();
            $icard->jrn=0;
            $icard->table=0;
            $icard->noadd="no";
            $icard->extra='all';
            $icard->name="e_march".$i;
            $tmp_ad=(isset($p_follow_up->aAction_detail[$i]))?$p_follow_up->aAction_detail[$i]:$readonly;
            $icard->readOnly=$readonly;
            $icard->value='';
            $aCard[$i]=0;
            if ($tmp_ad)
            {
                $march=new Fiche($p_follow_up->db);
                $f=$tmp_ad->get_parameter('qcode');
                if ($f!=0)
                {
                    $march->id=$f;
                    $icard->value=$march->get_quick_code();
                    $aCard[$i]=$f;
                }
            }
            $icard->set_dblclick("fill_ipopcard(this);");
            // name of the field to update with the name of the card
            $icard->set_attribute('label', "e_march".$i."_label");
            // name of the field to update with the name of the card
            $icard->set_attribute('typecard', $icard->extra);
            $icard->set_attribute('ipopup', 'ipopcard');
            $icard->set_function('fill_data');
            $icard->javascript=sprintf(' onchange="fill_data_onchange(\'%s\');" ', $icard->name);

            $aArticle[$i]['fid']=$icard->search().$icard->input();

            $text->javascript=' onchange="clean_tva('.$i.');compute_ledger('.$i.')"';
            $text->css_size="100%";
            $text->name="e_march".$i."_label";
            $text->id="e_march".$i."_label";
            $text->size=40;
            $text->value=($tmp_ad)?$tmp_ad->get_parameter('text'):"";
            $text->readOnly=$readonly;
            $aArticle[$i]['desc']=$text->input();

            $num->javascript=' onchange="format_number(this,4);clean_tva('.$i.');compute_ledger('.$i.')"';
            $num->name="e_march".$i."_price";
            $num->id="e_march".$i."_price";
            $num->size=8;
            $num->readOnly=$readonly;
            $num->value=($tmp_ad)?$tmp_ad->get_parameter('price_unit'):0;
            $aArticle[$i]['pu']=$num->input();

            $num->name="e_quant".$i;
            $num->id="e_quant".$i;
            $num->size=8;
            $num->value=($tmp_ad)?$tmp_ad->get_parameter('quantity'):0;
            $aArticle[$i]['quant']=$num->input();

            $itva->name='e_march'.$i.'_tva_id';
            $itva->id='e_march'.$i.'_tva_id';
            $itva->value=($tmp_ad)?$tmp_ad->get_parameter('tva_id'):0;
            $itva->readOnly=$readonly;
            $itva->js=' onchange="format_number(this);clean_tva('.$i.');compute_ledger('.$i.')"';
            $itva->set_attribute('compute', $i);

            $aArticle[$i]['tvaid']=$itva->input();

            $num->name="e_march".$i."_tva_amount";
            $num->id="e_march".$i."_tva_amount";
            $num->value=($tmp_ad)?$tmp_ad->get_parameter('tva_amount'):0;
            $num->javascript=" onchange=\"compute_ledger('".$i." ')\"";
            $num->size=8;
            $aArticle[$i]['tva']=$num->input();
            $tot_vat=bcadd($tot_vat, $num->value);

            $num->name="tvac_march".$i;
            $num->id="tvac_march".$i;
            $num->value=($tmp_ad)?$tmp_ad->get_parameter('total'):0;
            $num->size=8;
            $aArticle[$i]['tvac']=$num->input();
            $tot_item=bcadd($tot_item, $num->value);

            $aArticle[$i]['hidden_htva']=HtmlInput::hidden('htva_march'.$i, 0);
            $aArticle[$i]['hidden_tva']=HtmlInput::hidden('tva_march'.$i, 0);
            $aArticle[$i]['ad_id']=($tmp_ad)?HtmlInput::hidden('ad_id'.$i, $tmp_ad->get_parameter('id')):HtmlInput::hidden('ad_id'.$i,
                            0);
            ?>

            <TR>
                <TD><?php echo $aArticle[$i]['fid'] ?></TD>
                <TD><?php echo $aArticle[$i]['desc'] ?></TD>
                <TD class="num"><?php echo $aArticle[$i]['pu'] ?></TD>
                <TD class="num"><?php echo $aArticle[$i]['quant'] ?></TD>
                <TD class="num"><?php echo $aArticle[$i]['tvaid'] ?></TD>
                <TD class="num"><?php echo $aArticle[$i]['tva'] ?></TD>
                <TD class="num"><?php echo $aArticle[$i]['tvac'] ?>
                    <?php echo $aArticle[$i]['hidden_tva']; ?>
                    <?php echo $aArticle[$i]['hidden_htva']; ?>
                    <?php echo $aArticle[$i]['ad_id']; ?>
                </TD>
            </TR>
            <?php
        endfor;
        ?>
    </table>
    <div>

        <div style=" float:right;margin-right: 2px" id="sum">
            <br><span style="text-align: right;" class="highlight" id="htva"><?php echo bcsub($tot_item, $tot_vat) ?></span>
            <br><span style="text-align: right" class="highlight" id="tva"><?php echo $tot_vat ?></span>
            <br><span style="text-align: right" class="highlight" id="tvac"><?php echo $tot_item ?></span>
        </div>

        <div  style="float:right;margin-right: 230px" >
            <br>Total HTVA
            <br>Total TVA
            <br>Total TVAC
        </div>

    </div>
    <div id="d_add_rows">
    <?php echo Html_Input_Noalyss::ledger_add_item("O"); ?>
        
    <?php echo HtmlInput::button('actualiser', _('Recalculer'), ' onClick="compute_all_ledger();"'); ?>
    </div>
    <script>compute_all_ledger()</script>    
    <?php
    if (Document_Option::is_enable_make_invoice($p_follow_up->dt_id)):
        ?>
        <p id="follow_up_detail_invoice">
            <?php
            $query=http_build_query(array('gDossier'=>Dossier::id(), 'ag_id'=>$p_follow_up->ag_id, 'create_invoice'=>1, 'ac'=>$menu->get('code_invoice')));
            echo HtmlInput::button_anchor(_("Transformer en facture"), "do.php?".$query, "create_invoice",
                    '  target="_blank" ', "button");
            ?>
        </p>
        <?php
    endif;
    ?>