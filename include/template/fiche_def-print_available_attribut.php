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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 7/08/24
/*! 
 * \file
 * \brief available attribut for card category
 */

$js_add = sprintf("categoryCardDefinition.add_attribut('%s')",$attribut_id);
?>
<li id="avail_attr_<?= $attribut_id ?>" class="<?= $class ?>">
            <span>
                <?php

                ?>

                <span class="icon" onclick="<?= $js_add ?>">&#x1F808;</span>
            </span>


    <span class="search-content">

                <?php
                echo $attribut_text;
                ?>
            </span>
</li>