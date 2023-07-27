<?php
/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2002-2021) Author Dany De Bontridder <danydb@noalyss.eu>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief  show the mobile menu
 */
\Noalyss\Dbg::echo_file(__FILE__);
?>
<img src="<?=NOALYSS_URL?>/image/logo9000.png" width="100%" style="position:absolute;top:0px;left:0px;z-index:-1;opacity: 11%">
<div id="mobile_module"  >
<h1>NOALYSS</h1>
    <ul class="nav nav-pills nav-fill  flex-column  " >
        <?php
        foreach ($aModule as $row):
            $js="";
            $style="";

            $style="nav-item-module";
            if ($row['me_code']=='new_line')
            {
                continue;
            }
            if ($row['me_url']!='')
            {
                $url=$row['me_url'];
            }
            elseif ($row['me_javascript']!='')
            {
                $url="javascript:void(0)";
                $js_dossier=noalyss_str_replace('<DOSSIER>', Dossier::id(), $row['me_javascript']);
                $js=sprintf(' onclick="%s"', $js_dossier);
            }
            else
            {
                $url="mobile.php?gDossier=".Dossier::id()."&ac=".$row['me_code'];
            }
            ?>
            <li class="<?php echo $style ?>">
                <a class="nav-link border-1 border-dark rounded-2" href="<?php echo $url ?>" title="<?php echo _($row['me_description']) ?>" <?php echo $js ?> ><?php echo gettext($row['me_menu']) ?></a>
            </li>
            <?php
        endforeach;
        ?>
    </ul>
</div>
