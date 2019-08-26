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

// Copyright Author Dany De Bontridder danydb@noalyss.eu

/**
 * @file
 * @brief display the tax summary result
 * @see Tax_Summary
 *
 */?>
<h2><?php echo _("Vente")?></h2>

<?php
bcscale(4);
$array=$this->get_row_sale();
$nb_array=count($array);
$ledger="";
$r=0;
$tot_vat=0;$tot_wovat=0;$tot_sided=0;

for ($i=0;$i < $nb_array;$i++):

    if ( $ledger != $array[$i]['jrn_def_name']):
        if ( $ledger != "") :
            // last row with total
            echo '<tr class="highlight">';
            echo td();
            echo td();
            echo td(nbm($tot_wovat),' class="num" ');
            echo td(nbm($tot_vat),' class="num" ');
            echo td(nbm($tot_sided),' class="num" ');
            echo '</tr>';
            echo '</table>';
        endif;
        $ledger=$array[$i]['jrn_def_name'];
        //reinitialize sum
        $tot_vat=0;$tot_wovat=0;$tot_sided=0;

        printf("<h3>%s</h3>",$ledger);
        echo '<table class="result">';
        echo '<tr>';
        echo th(_("Code TVA"));
        echo th(_("Taux"),'class="num"');
        echo th(_("Montant HT"),'class="num"');
        echo th(_("Montant TVA"),'class="num"');
        echo th(_("Montant Autoliquidation"),'class="num"');
        echo '</tr>';
        $r=0;
    endif;
    $color=($r%2==0)?"even":"odd";
?>
<tr class="<?php echo $color;?>">
    <td>
        <?=$array[$i]['tva_label']?>
    </td>
    <td>
        <?=$array[$i]['tva_rate']*100?>%
    </td>
    <td class="num">
        <?=nbm($array[$i]['amount_wovat'])?>
    </td>
    <td class="num">
        <?=nbm($array[$i]['amount_vat'])?>
    </td>
    <td class="num">
        <?=nbm($array[$i]['amount_sided'])?>
    </td>
</tr>
    <?php
    $tot_vat=bcadd($tot_vat,$array[$i]['amount_vat']);
    $tot_wovat=bcadd($tot_wovat,$array[$i]['amount_wovat']);
    $tot_sided=bcadd($tot_sided,$array[$i]['amount_sided']);

    ?>
<?php endfor;?>
<?php
if ( $nb_array > 0):
// last row with total
    echo '<tr class="highlight">';
    echo td();
    echo td();
    echo td(nbm($tot_wovat),' class="num" ');
    echo td(nbm($tot_vat),' class="num" ');
    echo td(nbm($tot_sided),' class="num" ');
    echo '</tr>';
else:
    echo _("Aucune donnée");
endif;
    echo '</table>';
?>
<h3><?=_("Résumé tous les journaux de vente")?></h3>
<?php
$a_sum=$this->get_summary_sale();
?>
<table class="result">
    <tr>
        <th>
            <?=_("Code TVA")?>
        </th>
        <th>
            <?=_("Taux")?>
        </th>
        <th  class="num">
            <?=_("Montant HT")?>
        </th>
        <th class="num">
            <?=_("Montant TVA")?>
        </th>
        <th class="num">
            <?=_("Montant Autoliquidation")?>
        </th>
    </tr>
    <?php
    $nb_sum=count($a_sum);
    $tot_vat=0;$tot_wovat=0;$tot_sided=0;

    for ($e=0;$e < $nb_sum ; $e++):
        $tot_vat=bcadd($tot_vat,$a_sum[$e]['amount_vat']);
        $tot_wovat=bcadd($tot_wovat,$a_sum[$e]['amount_wovat']);
        $tot_sided=bcadd($tot_sided,$a_sum[$e]['amount_sided']);

        ?>
    <tr>

        <td>
            <?=$a_sum[$e]['tva_label']?>
        </td>
        <td  class="num">
            <?=$a_sum[$e]['tva_rate']*100?>%
        </td>
        <td class="num">
            <?=nbm($a_sum[$e]['amount_wovat'])?>
        </td>
        <td class="num">
            <?=nbm($a_sum[$e]['amount_vat'])?>
        </td>
        <td class="num">
            <?=nbm($a_sum[$e]['amount_sided'])?>
        </td>
    </tr>
<?php
    endfor;

    echo '<tr class="highlight">';
        echo td();
        echo td();
        echo td(nbm($tot_wovat),' class="num" ');
        echo td(nbm($tot_vat),' class="num" ');
        echo td(nbm($tot_sided),' class="num" ');
        echo '</tr>';
        ?>
</table>
<?php
$array =    $this->get_summary_sale();


?>
<h2><?php echo _("Achat")?></h2>
<?php
$array=$this->get_row_purchase();
$nb_array=count($array);
$ledger="";
$r=0;
$tot_vat=0;$tot_wovat=0;$tot_sided=0;$tot_noded_amount=0;$tot_noded_tax=0;$tot_noded_return=0;$tot_private=0;
for ($i=0;$i < $nb_array;$i++):

    if ( $ledger != $array[$i]['jrn_def_name']):
        if ( $ledger != "") :
            // last row with total
            echo '<tr class="highlight">';
            echo td();
            echo td();
            echo td(nbm($tot_wovat),' class="num" ');
            echo td(nbm($tot_private),' class="num" ');
            echo td(nbm($tot_vat),' class="num" ');
            echo td(nbm($tot_sided),' class="num" ');
            echo td(nbm($tot_noded_amount),' class="num" ');
            echo td(nbm($tot_noded_tax),' class="num" ');
            echo td(nbm($tot_noded_return),' class="num" ');
            echo '</tr>';
            echo '</table>';
        endif;
        $ledger=$array[$i]['jrn_def_name'];
        //reinitialize sum
        $tot_vat=0;$tot_wovat=0;$tot_sided=0;$tot_noded_amount=0;$tot_noded_tax=0;$tot_noded_return=0;$tot_private=0;

        printf("<h3>%s</h3>",$ledger);
        echo '<table class="result">';
        echo '<tr>';
        echo th(_("Code TVA"));
        echo th(_("Taux"),'class="num"');
        echo th(_("Montant HT"),'class="num"');
        echo th(_("Privée"),'class="num"');
        echo th(_("Montant TVA"),'class="num"');
        echo th(_("Montant Autoliquidation"),'class="num"');
        echo th(_("Montant Non Déd"),'class="num"');
        echo th(_("TVA Non Déd"),'class="num"');
        echo th(_("TVA Non Déd & récup"),'class="num"');
        echo '</tr>';
        $r=0;
    endif;
    $color=($r%2==0)?"even":"odd";
    ?>
    <tr class="<?php echo $color;?>">
        <td>
            <?=$array[$i]['tva_label']?>
        </td>
        <td>
            <?=$array[$i]['tva_rate']*100?>%
        </td>
        <td class="num">
            <?=nbm($array[$i]['amount_wovat'])?>
        </td>
        <td class="num">
            <?=nbm($array[$i]['amount_private'])?>
        </td>
        <td class="num">
            <?=nbm($array[$i]['amount_vat'])?>
        </td>
        <td class="num">
            <?=nbm($array[$i]['amount_sided'])?>
        </td>
        <td class="num">
            <?=nbm($array[$i]['amount_noded_amount'])?>
        </td>
        <td class="num">
            <?=nbm($array[$i]['amount_noded_tax'])?>
        </td>
        <td class="num">
            <?=nbm($array[$i]['amount_noded_return'])?>
        </td>
    </tr>
    <?php
    $tot_vat=bcadd($tot_vat,$array[$i]['amount_vat']);
    $tot_wovat=bcadd($tot_wovat,$array[$i]['amount_wovat']);
    $tot_sided=bcadd($tot_sided,$array[$i]['amount_sided']);
    $tot_noded_amount=bcadd($tot_noded_amount,$array[$i]['amount_noded_amount']);
    $tot_noded_tax=bcadd($tot_noded_tax,$array[$i]['amount_noded_tax']);
    $tot_noded_return=bcadd($tot_noded_return,$array[$i]['amount_noded_return']);
    $tot_private=bcadd($tot_private,$array[$i]['amount_private']);

    ?>
<?php endfor;?>
<?php
// last row with total
    if ( $nb_array > 0):
    echo '<tr class="highlight">';
    echo td();
    echo td();
    echo td(nbm($tot_wovat),' class="num" ');
    echo td(nbm($tot_private),' class="num" ');
    echo td(nbm($tot_vat),' class="num" ');
    echo td(nbm($tot_sided),' class="num" ');
    echo td(nbm($tot_noded_amount),' class="num" ');
    echo td(nbm($tot_noded_tax),' class="num" ');
    echo td(nbm($tot_noded_return),' class="num" ');
    echo '</tr>';
else:
    echo _("Aucune donnée");
endif;
echo '</table>';
?>
<h3><?=_("Résumé tous les journaux d'achat")?></h3>
<?php
$a_sum=$this->get_summary_purchase();
?>
<table class="result">
    <tr>
        <th>
            <?=_("Code TVA")?>
        </th>
        <th>
            <?=_("Taux")?>
        </th>
        <th  class="num">
            <?=_("Montant HT")?>
        </th>
        <th  class="num">
            <?=_("Privée")?>
        </th>
        <th class="num">
            <?=_("Montant TVA")?>
        </th>
        <th class="num">
            <?=_("Montant Autoliquidation")?>
        </th>
        <th class="num">
            <?=_("Montant Non Déd")?>
        </th>
        <th class="num">
            <?=_("TVA Non Déd")?>
        </th>
        <th class="num">
            <?=_("TVA Non Déd & récup")?>
        </th>
    </tr>
    <?php
    $nb_sum=count($a_sum);
    $tot_vat=0;$tot_wovat=0;$tot_sided=0;$tot_noded_amount=0;$tot_noded_tax=0;$tot_noded_return=0;$tot_private=0;

    for ($e=0;$e < $nb_sum ; $e++):

        ?>
        <tr>

            <td>
                <?=$a_sum[$e]['tva_label']?>
            </td>
            <td  class="num">
                <?=$a_sum[$e]['tva_rate']*100?>%
            </td>
            <td class="num">
                <?=nbm($a_sum[$e]['amount_wovat'])?>
            </td>
            <td class="num">
                <?=nbm($a_sum[$e]['amount_private'])?>
            </td>
            <td class="num">
                <?=nbm($a_sum[$e]['amount_vat'])?>
            </td>
            <td class="num">
                <?=nbm($a_sum[$e]['amount_sided'])?>
            </td>
            <td class="num">
                <?=nbm($a_sum[$e]['amount_noded_amount'])?>
            </td>
            <td class="num">
                <?=nbm($a_sum[$e]['amount_noded_tax'])?>
            </td>
            <td class="num">
                <?=nbm($a_sum[$e]['amount_noded_return'])?>
            </td>
        </tr>
    <?php
        $tot_vat=bcadd($tot_vat,$a_sum[$e]['amount_vat']);
        $tot_wovat=bcadd($tot_wovat,$a_sum[$e]['amount_wovat']);
        $tot_sided=bcadd($tot_sided,$a_sum[$e]['amount_sided']);
        $tot_noded_amount=bcadd($tot_noded_amount,$a_sum[$e]['amount_noded_amount']);
        $tot_noded_tax=bcadd($tot_noded_tax,$a_sum[$e]['amount_noded_tax']);
        $tot_noded_return=bcadd($tot_noded_return,$a_sum[$e]['amount_noded_return']);
        $tot_private=bcadd($tot_private,$a_sum[$e]['amount_private']);

    endfor;

    echo '<tr class="highlight">';
        echo td();
        echo td();
        echo td(nbm($tot_wovat),' class="num" ');
        echo td(nbm($tot_private),' class="num" ');
        echo td(nbm($tot_vat),' class="num" ');
        echo td(nbm($tot_sided),' class="num" ');
        echo td(nbm($tot_noded_amount),' class="num" ');
        echo td(nbm($tot_noded_tax),' class="num" ');
        echo td(nbm($tot_noded_return),' class="num" ');
        echo '</tr>';
        ?>
</table>
