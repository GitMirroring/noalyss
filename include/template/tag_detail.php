<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
?><?php
echo Dossier::hidden();
echo HtmlInput::hidden('t_id',$data->t_id);
echo HtmlInput::hidden('ac',$_REQUEST['ac']);
$uos=new Single_Record('tag');
echo $uos->hidden();
$t_tag=new IText('t_tag',$data->t_tag);
$t_description=new ITextarea('t_description',$data->t_description);
$t_description->style=' class="itextarea" style="height:5em;vertical-align: top;"';
$t_actif=new ISelect("t_actif");
$t_actif->value=[
    ['label'=>_('Actif'),'value'=>'Y'],
    ['label'=>_('Non actif'),'value'=>'N']
    ];
$t_actif->selected=$data->t_actif;
$icheckbox=new ICheckBox("tagcell_color");
$icheckbox->javascript='onclick="uncheck_other(this,\'tagcell_color\');"';
?>
<p>
   <?php echo _("Etiquette (tag)")?> : <?php echo $t_tag->input(); ?>
</p>
<p>
<?php echo _("Description")?> : <?php echo $t_description->input(); ?>
</p>
<p>
    <?=_("Couleur")?>
</p>
<?php
if ( $data->t_id == '-1') $data->t_color=1;
$nb_color=36;

for ($i=1 ; $i != $nb_color+1 ; $i++ ) {
?>    
        <span class="tagcell <?="tagcell-color".$i?>">
            <?=_("Exemple").$i ?>
            <?php $icheckbox->value=$i;
            if ( $data->t_color==$i) { $icheckbox->set_check($i);} else {$icheckbox->selected=false;}
            echo $icheckbox->input();
            ?>
        </span>
<?php 

} // end loop $i
?>
  
<p>
    <?=_("Etiquette actif") ?><?=$t_actif->input()?>
</p>
<?php
// If exist you can remove it
if ( $data->t_id != '-1') : 
?>
<p><?php echo _("Cochez pour cette case pour effacer cette étiquette (tag)")?><input type="checkbox" name="remove">
</p>

<?php
endif;
?>
