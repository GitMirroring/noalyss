<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
$nb_array=count($array);
?>
<fieldset id="asearch" style="height:41rem">
    <legend><?php echo _('Résultats'); ?></legend>
    <div style="height:100%;overflow:auto;">
        <?php if ($nb_array > 10) :
                echo _("recherche rapide");
                echo HtmlInput::filter_table("card_list", '1,2,3', 0);
             endif;
             ?>
        <?php printf (_("Nombre de fiches trouvées %d montrées %d"),$count_card,$nb_array);?>
        <table id="card_list" class="result" >
            <th></th>
            <th><?=_("QCode")?></th>
            <th><?=_("Société")?></th>
            <th><?=_("Nom")?></th>
            <th><?=_("Description")?></th>
            <?php for ($i=0; $i<$nb_array; $i++) : ?>
                <?php $class=($i%2==0)?'odd':'even'; ?>
                <tr class="<?php echo $class; ?>">
                    <td style="padding-right:55px">
                        <?php echo $array[$i]['checkbox'] ?>
                    </td>
                    <td style="padding-right:55px">
                        <?php echo $array[$i]['quick_code'] ?>
                    </td>
                    <td>
                        <?php echo $array[$i]['company'] ?>
                    </td>
                    <td>
                        <?php echo $array[$i]['name'] ?>
                        <?php echo $array[$i]['first_name'] ?>
                    </td>
                    <td>
                        <?php echo $array[$i]['description'] ?>
                    </td>

                </tr>


            <?php endfor; ?>
        </table>
        <span style="font-style: italic;">
            <?php echo _("Nombre d'enregistrements:$i"); ?>
        </span>
        <br>
    </div>
</fieldset>
