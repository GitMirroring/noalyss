<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
?>
<div class="rpm_main">
    <?php
    for ($i = 0; $i < count($array); $i++) {
        ?>
        <div class="rpm_operation rpm_title">
        <div >
            <?php echo _("N°") ?>
        </div>
        <div >
            <?php echo _("Date") ?>
        </div>
        <div>
            <?php echo _("Interne") ?>
        </div>
        <div>
            <?php echo _("N° pièce") ?>
        </div>
        <div> 
            <?=_("Tiers")?>
        </div>
        <div>
            <?php echo _("Libellé") ?>
        </div>
        <div>
            <?php echo _("Montant") ?>
        </div>
        <div></div>
    </div>
        <div class="rpm_operation ">
            <div><?= $i ?></div>
            <div><?=$array[$i]['str_jr1_jr_date']?></div>
            <div><?= HtmlInput::detail_op($array[$i]['jr1_jr_id'], $array[$i]['jr1_jr_internal']) ?></div>
            <div><?= $array[$i]['jr1_jr_pj_number'] ?></div>
            <div><?=HtmlInput::card_detail($array[$i]['tiers_qcode'])?></div>
            <div><?= $array[$i]['jr1_jr_comment'] ?></div>
            <?php
            $x=($array[$i]['to1_sum_amount']!=0)?$array[$i]['to1_sum_amount']:$array[$i]['jr1_jr_montant'];
            ?>
            <div><?=nbm($x)?></div>
        
            <div class="rpm_detail">
                <?php
                $r = '';

                // check if operation does exist in v_detail_quant
//                $ret = $acc_reconciliation->db->execute('detail_quant', array($array[$i]['jr1_jr_id']));
//                echo '<table>';
//                $acc_reconciliation->show_detail($ret);
//                echo '</table>';
                
                if ( $array[$i]['depend_count']>0) {
                    $depend=$acc_reconciliation->db->get_array("select * 
                        from temp_total_operation 
                        where 
                            jr1_jr_id=$1 and ra1_jra_concerned != jr1_jr_id"
                            ,[$array[$i]['jr1_jr_id']]);
                    $nb_depend = count($depend);
                    $totdepend=0;$delta=$x;
                    ?>
                        <div class="rpm_detail">
                            <h4><?=_("Opération liée")?></h4>
                    <?php
                    for ($e = 0; $e < $nb_depend ; $e++) {
                        $y=($depend[$e]['to2_sum_amount']!=0)?$depend[$e]['to2_sum_amount']:$depend[$e]['jr2_jr_montant'];
                        $totdepend=bcadd($totdepend,$y,2);
                        $delta=bcsub($delta,$y,2);
                        ?>
                        <div class="rpm_operation">
                        <div><?= $i ?></div>
                        <div><?=$depend[$e]["str_jr2_jr_date"] ?></div>
                        <div><?= HtmlInput::detail_op($depend[$e]["ra1_jra_concerned"],$depend[$e]["jr2_jr_internal"]) ?></div>
                        <div><?=$depend[$e]["jr2_jr_pj_number"] ?></div>
                        <div><?=HtmlInput::card_detail($array[$i]['tiers_qcode_2'])?></div>
                        <div><?=$depend[$e]["jr2_jr_comment"] ?></div>
                        <div><?= nbm($y) ?></div>
                        
                        <?php

                       
                       ?></div>
                            
                       <?php
                        } // end for ($e = 0; $e < count($array[$i]['depend']); $e++) 

                        ?></div>
                <div class="rpm_detail highlight">
                    <?=sprintf(_("Total opération liée : %.2f Delta %.2f"),$totdepend,$delta)?>
                </div>
                            <?php
                } //   if ( $array[$i]['depend_count']>1)
                ?>
            </div>
        </div>
            <?php
        } // end for ($i=0;$i<count($array);$i++)
        ?>
</div>