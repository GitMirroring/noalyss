<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
?>
<fieldset id="asearch" style="height:88%">
   <legend><?php echo _('Résultats'); ?></legend>
<div style="height:88%;overflow:auto;">
    <?php
    // compute the url, $sql_array is defined in ajax_card.php
    echo '<ul  class="aligned-block" style="padding:1px;margin-top: 0px;margin-bottom: 0px;">';
    if ( $page_card != 0 ) {
        // show previous arrow to left
        echo '<li style="padding:1px">';
        echo '<form method="GET" onsubmit="this.ctl=\'ipop_card\';search_get_card(this);return false;">';           
        $url=$sql_array;
        $url["page_card"]=$page_card-1;
        echo HtmlInput::array_to_hidden(array('accvis','inp','jrn','label','typecard','price','tvaid','amount_from_type','page_card','query','inactive_card'), $url);
        echo HtmlInput::submit("previous","<");
        echo '</form>';
        echo '</li>';
    }
    if ( $record_start < $total_card && $nb_found >= MAX_SEARCH_CARD) {
        echo '<li style="padding:1px">';
         echo '<form method="GET" onsubmit="this.ctl=\'ipop_card\';search_get_card(this);return false;">';           
         $url=$sql_array;
        $url["page_card"]=$page_card+1;
        echo HtmlInput::array_to_hidden(array('accvis','inp','jrn','label','typecard','price','tvaid','amount_from_type','page_card','query','inactive_card'), $url);
        
        echo HtmlInput::submit("next",">");
        echo '</form>';
        echo '</li>';
    }
    echo '</ul>';
 ?>

<table class="result" >
<?php for ($i=0;$i<sizeof($array);$i++) : ?>
    <?php $class=($i%2==0)?'odd':'even';?>
<tr class="<?php echo $class;?>">
    <td style="padding-right:55px">
<a href="javascript:void(0)" class="detail" onclick="<?php echo $array[$i]['javascript']?>">
<?php echo $array[$i]['quick_code']?>
</a>
</td>
<?php 
//---------------------------------------------------------------------------
// if accvis == 1 then show the accounting 
//---------------------------------------------------------------------------
if ( $accvis == 1 ) :
    ?>
<td>
   <?php echo HtmlInput::history_account($array[$i]['accounting'],$array[$i]['accounting']); ?>
</td>
<?php endif;?>
<td>
   <?php echo $array[$i]['name']?>
</td>
<td>
   <?php echo $array[$i]['first_name']?>
</td>
<td>
<?php echo $array[$i]['description']?>
</td>

</tr>


<?php endfor; ?>
</table>
<span style="font-style: italic;">
    <?php echo _("Nombre d'enregistrements trouvé:$total_card"); ?>
</span>
<br>
</div>
</fieldset>
