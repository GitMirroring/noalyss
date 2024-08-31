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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 2/08/24
/*! 
 * \file
 * \brief for new category of cards, proposed the template on which the category of card will  be based
 */
?>
<h3><?php echo _("Modèles de catégorie")?></h3>
<p class="text-muted">
    <?php echo _("Choisissez un modèle pour cette nouvelle catégorie de fiche")?>
</p>
<ul id="template_category_ck">
    <?php
    if ( !empty ($ref)  ) {
        foreach ($ref as $i=>$v) { ?>
            <li style="list-style-type: none">
            <?php echo $iradio->input("FICHE_REF",$v['frd_id']);
            echo $v['frd_text'];
            if ( !empty ($v['frd_class_base']) != 0 )
                echo "&nbsp;&nbsp<I>Class base = ".$v['frd_class_base']."</I>";

        }?>
        </li>
    <?php }
    ?>
</UL>
