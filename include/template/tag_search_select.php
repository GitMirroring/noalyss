<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
?><?php


$max=$this->cn->count($ret);
if ( $max == 0 ) {
    $res=h2(_("Aucune étiquette disponible"),' class="notice"').
            HtmlInput::button_close($p_prefix.'tag_div');
    return $res;
    
}
require_once NOALYSS_INCLUDE."/lib/output_html_tab.class.php";
$tab_tag=new Html_Tab($p_prefix."tab_tag",_("Etiquettes"));
ob_start();
?>
<?php echo _("Cherche")." ".HtmlInput::filter_table($p_prefix.'tag_tb_id', '0,1', 1); ?>
<?php echo HtmlInput::button_action(_('Uniquement actif'), 'show_only_row(\''.$p_prefix.'tag_tb_id'.'\',\'tag_status\',\'Y\')');?>
<?php echo HtmlInput::button_action(_('Tous'), 'show_all_row(\''.$p_prefix.'tag_tb_id'.'\')');?>
<table class="result" id="<?php echo $p_prefix;?>tag_tb_id">
    <tr>
        <th>
            <?php echo _("Tag")?>
        </th>
        <th>
            <?php echo _("Description")?>
        </th>
    </tr>
<?php
$gDossier=Dossier::id();
    for ($i=0;$i<$max;$i++):
        $row=Database::fetch_array($ret, $i);
    $attr=sprintf('tag_status="%s"',$row['t_actif']);
?>
    <tr <?=$attr?> class="<?php echo (($i%2==0)?'even':'odd');?>">
        <td>
            <?php
            $js=sprintf("search_add_tag('%s','%s','%s','t')",$gDossier,$row['t_id'],$p_prefix);
            echo HtmlInput::anchor($row['t_tag'], "", "onclick=\"$js\"");
            ?>
        </td>
        <td>
            <?php
            echo h($row['t_description']);
            ?>
        </td>
         <td>
            <?php
            if ( $row['t_actif'] == 'N') { 
                echo _('non actif');
            }
            ?>
        </td>
    </tr>
<?php
 endfor;
 ?>
</table>
 
<?php 
  $tab_tag->set_content(ob_get_contents());
   ob_end_clean();
// Tab for the groups 
//
ob_start();
$tab_tag_group=new Html_Tab($p_prefix."tab_tag_group",_("Groupe"));
$aGroup=$this->cn->get_array("select tg_id, tg_name from tag_group order by 2 desc");
$nb_agroup=count($aGroup);
?>
<table class="result">
    <tr>
        <th><?=_("Groupe")?></th>
    </tr>
    <?php for ($i=0;$i<$nb_agroup;$i++):?>
    <tr>
        <td>
            <?php
            $js=sprintf("search_add_tag('%s','%s','%s','g')",$gDossier,$aGroup[$i]['tg_id'],$p_prefix);
            echo HtmlInput::anchor($aGroup[$i]['tg_name'], "", "onclick=\"$js\"");
            ?>
        </td>
            
    </tr>
   <?php endfor;?>
</table>
<?php


$tab_tag_group->set_content(ob_get_contents());
ob_end_clean();

// Both tab
$output=new Output_Html_Tab();
$output->add($tab_tag);
$output->add($tab_tag_group);
ob_start();
$output->output();

?>

<?=HtmlInput::button_close($p_prefix.'tag_div')?>

<script>
    show_only_row('<?=$p_prefix?>tag_tb_id','tag_status','Y');
    <?=$output->build_js($p_prefix."tab_tag");?>
</script>   
<?php 
$res =ob_get_contents();
ob_end_clean();
return $res;
?>