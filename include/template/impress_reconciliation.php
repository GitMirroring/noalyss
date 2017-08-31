<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
?><table class="result">
<tr>
<th>
<?php echo _("N°")?>
</th>
<th>
<?php echo _("Date")?>
</th>
<th>
<?php echo _("N° op")?>
</th>
<th>
<?php echo _("n° pièce")?>
</th>
<th>
<?php echo _("Libellé")?>
</th>
<th style="text-align:right">
<?php echo _("Montant")?>
</th>
</tr>
<?php 
for ($i=0;$i<count($array);$i++) {
        $tot=$array[$i]['first']['jr_montant'];
        $retdb=$cn->execute("detail_quant",array($array[$i]['first']['jr_id']));
        if ( Database::num_row($retdb) != 0)
        {
            // then second_amount takes in account the vat_sided
            $row=Database::fetch_array($retdb, 0);
            $total_price=bcadd($row['price'],$row['vat_amount']);
            $total_price=bcsub($total_price,$row['vat_sided']);
            $tot=$total_price;

        }
	$r='';
	$r.=td($i);
	$r.=td(format_date($array[$i]['first']['jr_date']));
        $detail = HtmlInput::detail_op($array[$i]['first']['jr_id'], $array[$i]['first']['jr_internal']);
	$r.=td($detail);
	$r.=td($array[$i]['first']['jr_pj_number']);
	$r.=td($array[$i]['first']['jr_comment']);
	$r.=td(nbm($tot),'style="text-align:right"');
	echo tr($r);
        // check if operation does exist in v_detail_quant
        $ret=$acc_reconciliation->db->execute('detail_quant',array($array[$i]['first']['jr_id']));
        $acc_reconciliation->show_detail($ret);
	if ( isset($array[$i]['depend']) )
	{
            $tot2=0;
            $limit=count($array[$i]['depend'])-1;
            for ($e=0;$e<count($array[$i]['depend']);$e++) {
                    $r='';
                    $r.=td($i);
                    $r.=td(format_date($array[$i]['depend'][$e]['jr_date']));
                    $detail = HtmlInput::detail_op($array[$i]['depend'][$e]['jr_id'], $array[$i]['depend'][$e]['jr_internal']);
                    $r.=td($detail);
                    $r.=td($array[$i]['depend'][$e]['jr_pj_number']);
                    $r.=td($array[$i]['depend'][$e]['jr_comment']);
                    $r.=td(nbm($array[$i]['depend'][$e]['jr_montant']),'style="text-align:right"');
                    $retdb=$cn->execute("detail_quant",array($array[$i]['depend'][$e]['jr_id']));
                    // if exist in v_quant_detail
                    if ( Database::num_row($retdb) != 0)
                    {
                        // then second_amount takes in account the vat_sided
                        $row=Database::fetch_array($retdb, 0);
                        $total_price=bcadd($row['price'],$row['vat_amount']);
                        $total_price=bcsub($total_price,$row['vat_sided']);
                        $tot2=bcadd($tot2,$total_price);

                    } else {
                    // else take the amount from jrn
                      $tot2=bcadd($tot2,$array[$i]['depend'][$e]['jr_montant']);
                    }
                    if ( $e==$limit)
                            echo '<tr>'.$r.'</tr>';
                    else
                            echo tr($r);
                    $ret=$acc_reconciliation->db->execute('detail_quant',array($array[$i]['depend'][$e]['jr_id']));
                    $acc_reconciliation->show_detail($ret);
                    }
           echo tr(td(_('Total ')).td(_('operation')).td(nbm($tot)).td(_('operations dépendantes')).td(nbm($tot2)).td(_('Delta')).td(bcsub($tot,$tot2)),' class="highlight"');
           echo tr(td('<hr>',' colspan="6" style="witdh:auto"'));                        
                         
	}
}
?>
</table>