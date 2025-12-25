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
    <?php Noalyss\Dbg::echo_file(__FILE__);?>
<div class="row">
    <div class="col">
        <h3>Attributs de la classe</h3>
        <ul id="attribut_card" class="list-unstyled" style="cursor: move;">
            <?php
            $i = 0;
            foreach ($existing_attribut as $item):

                Fiche_Def::print_existing_attribut($item['ad_id'], $item['ad_text']);
                ?>


            <?php
            endforeach;
            ?>
        </ul>
    </div>
    <div class=" col-2">
        <h4> Ranger les attributs</h4>
        <p>
            Déplacer les attributs de la fiche en cliquant et déplacer sans relacher le bouton de la souris.
        </p>

        <p>
            Supprimer un attribut n'est pas réversible:() les données de ces attributs
            seront définitivement perdus.
        </p>
        <p>
            Ajouter des attributs depuis les attributs disponibles en cliquant sur la flèche.
        </p>
        <p>
            <input type="button" class="button" onclick="categoryCardDefinition.save();return false" value="Sauve">
        </p>

    </div>

    <div class="col border-dark">
        <h3>Attributs disponibles</h3>
        <?php
        echo HtmlInput::filter_list("avail_attribut_id", '0', '0');
        ?>
        <ul id="avail_attribut_id" style="width: 90%" class="list-unstyled">
            <?php
            $i = 0;
            foreach ($available_attribut as $item):
                $class = ($i % 2 == 0) ? 'even' : 'odd';
                $i++;
                Fiche_Def::print_available_attribut($item['ad_id'], $item['ad_text'], $class);
            ?>
            <?php
            endforeach;
            ?>

        </ul>

    </div>

</div>
</div>
<div class="row">

    <script>

        (function () {
            Sortable.create('attribut_card', {tag: 'li',hoverclass:'hoverclass-drag'});
        })();

        var categoryCardDefinition=new CategoryCardDefinition(<?=Dossier::id()?>,<?=$this->id?>);
    </script>
