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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 4/08/24
/*! 
 * \file
 * \brief manage attribut
 */
$cn = Dossier::connect();

$existing_attribut = $cn->get_array('
                select jnt_id,ad_id,ad_text,jnt_order 
                from attr_def 
                    join jnt_fic_attr jfa using (ad_id  ) 
                where
                    fd_id=$1 order by jnt_order ', [$this->id]);

$available_attribut = $cn->get_array('
                select ad_id,ad_text,ad_default_order 
                from attr_def 
                where 
                    ad_id not in (select ad_id from jnt_fic_attr jfa where fd_id=$1) order by 2', [$this->id]);

?>

<div class="row">
    <div class="col">
        <h3>Attributs de la classe</h3>
<div id="attribut_card">
        <?php
        $i = 0;
        foreach ($existing_attribut

        as $item):
        $class = ($i % 2 == 0) ? 'even' : 'odd';
        $i++;
        ?>

        <div id="attr_<?=$item['jnt_id']?>" style="cursor: move;" class="<?= $class ?>" order="<?= $item['jnt_order'] ?>">


            <?php
            echo $item['ad_text'];
            ?>
            <div style="float:right">
             <?=\Icon_Action::trash("0","")?>
            </div>
        </div>
            <?php
            endforeach;
            ?>
</div>
    </div>
    <div class=" col-2">
        <h4> Ranger les attributs</h4>
        <p>
            Supprimer un attribut n'est pas réversible: les données de ces attributs
            seront définitivement perdus.
        </p>
        <!--        <input type="submit" class="button" value="Sauver les attributs">-->
    </div>

<div class="col border-dark">
    <h3>Attributs disponibles</h3>
    <?php
    echo HtmlInput::filter_table("avail_attribut_id", '0', '0');
    ?>
    <table id="avail_attribut_id" style="width: 90%">
        <?php
        $i = 0;
        foreach ($available_attribut          as $item):
        $class = ($i % 2 == 0) ? 'even' : 'odd';
        $i++;
        ?>
        <tr class="<?= $class ?>">
            <td>
                <?php
                // ajout de l'attribut donc cette ligne disparait, et apparait de l'autre cote + maj db
                $js_add=sprintf("f")
                ?>

                <span class="icon" onclick="<?=$js_add?>">&#x21e6;</span>
            </td>


            <td>

                <?php
                echo $item['ad_text'];
                ?>
            </td>
            <?php
            endforeach;
            ?>

    </table>

</div>

</div>
<div class="row">

</div>
<script>

(function() {

    Sortable.create('attribut_card',{tag:'div',onChange:function(e) {console.debug(e)},onUpdate:function(e) { console.debug(e)}});

})();
</script>
