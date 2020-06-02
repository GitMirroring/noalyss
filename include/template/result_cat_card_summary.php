<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
?><div class="content">
<?php echo _("Cherche")?> :    
    <?php
    $col="";$sp="";
    for ($e=0;$e<count($aHeading);$e++) {$col.=$sp.$e; $sp=",";}
    echo HtmlInput::filter_table("fiche_tb_id", $col, '1'); 
    ?>
<table id="fiche_tb_id" class="sortable">
<tr>
<?php 
   echo th(_('Détail'));
for ($i=0;$i<count($aHeading);$i++) :
    $span="";$sort="";
   if ($i==0)
   {
       $sort= 'class="sorttable_sorted"';
   }
   echo '<th '.$sort.'>'.$aHeading[$i]->ad_text.'</th>';
   endfor;
?>
</tr>
<?php 
$e=0;
 $fiche=new Fiche($cn);
 echo HtmlInput::hidden("card_gdossier",Dossier::id());
foreach ($array as $row ) :
 $e++;
   if ($e%2==0)
   printf('<tr id="row_card%s" class="odd">',$row['f_id']);
   else 
   printf('<tr id="row_card%s" class="even">',$row['f_id']);
   
  $fiche->id=$row['f_id'];
  $detail=Icon_Action::modify("mod".$fiche->id, sprintf("modify_card('%s')",$fiche->id)).
          "&nbsp;".
          Icon_Action::trash("del".$fiche->id,  sprintf("delete_card_id('%s')",$fiche->id));
echo td($detail);
$fiche->display_row();

 echo '</tr>';
endforeach;

?>
</table>



</div>
