
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
// Copyright (2024) Author Dany De Bontridder <dany@alchimerys.be>
/**
 * @file
 * @brief : display all  available widgets for the connected user
 */
global $g_user,$cn;
use \Noalyss\Widget\Widget;

$aWidget=\Noalyss\Widget\Widget::get_enabled_widget();


$nb=count($aWidget);
?>
<div id="dashboard_div_id">

<?php
for ($i=0;$i < $nb ;$i++):
    $widget=Widget::build_user_widget($aWidget[$i]['uw_id'],$aWidget[$i]['wd_code']);
    Widget::ajax_display($widget);

endfor;
    ?>
</div>

