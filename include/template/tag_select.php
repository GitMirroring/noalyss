<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
/**
 * @file
 * @brief list of tags
 */
$http=new HttpInput();
?>
<?php
echo HtmlInput::title_box('Tag', 'tag_div');
$max=$this->cn->count($ret);
if ( $max == 0 ) {
    echo h2(_("Aucune étiquette disponible"),' class="notice"');
    return;
}
?>
<?php echo  _("Cherche")." ".HtmlInput::filter_table('tag_tb_id', '0,1', 1); ?>
<table class="result"  id="tag_tb_id">
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
$id="none";

if ( get_class($this) == 'Tag_Action' ) {
    $id=$http->request("ag_id","number");
 } elseif (get_class($this)=='Tag_Operation')
{
     $id=$http->request("jrn_id");
}

if (isNumber($id) == 0 ) die ('ERROR : parameters invalid');
    for ($i=0;$i<$max;$i++):
        $row=Database::fetch_array($ret, $i);
?>
    <tr class="<?php echo (($i%2==0)?'even':'odd');?>">
        <td class="tagcell-color<?=$row['t_color']?>">
            <?php
            if ( get_class($this) == 'Tag_Action' ) {
                $js=sprintf("action_tag_add('%s','%s','%s','%s')",$gDossier,$id,$row['t_id'],$row['tag_type']);
            } else {
                $js=sprintf("new operation_tag('%s').add('%s','%s','%s','%s')",$p_prefix,$gDossier,$id,$row['t_id'],$row['tag_type']);
            }
            
            echo HtmlInput::anchor($row['t_tag'], "", "onclick=\"$js\"");
            ?>
        </td>
        <td>
            <?php
            if ( $row['t_description'] != "G"):
                echo $row['t_description'];
            else :
                echo '<span class="icon">&#xe83e;</span>';
            endif;
            ?>
        </td>
    </tr>
<?php
 endfor;
 ?>
</table>