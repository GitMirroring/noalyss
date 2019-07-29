<?php

/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
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
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2018) Author Dany De Bontridder <dany@alchimerys.be>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief Display all cards using an accounting
 */
$accounting=$http->get("p_accounting");

$a_card=$cn->get_array("
    select f1.f_id,f3.ad_value as qcode, f2.ad_value as name
        from fiche_detail as f1 
        join fiche_detail as f2 on (f1.f_id=f2.f_id and f2.ad_id=1)
        join fiche_detail as f3 on (f1.f_id=f3.f_id and f3.ad_id=23)
    where 
        f1.ad_id=5 and f1.ad_value=$1
        order by 3", [$accounting]);
$nb_card=count($a_card);
echo HtmlInput::title_box($accounting, "info_card_accounting");
echo '<div class="content">';
echo '<ol>';
for ($i=0; $i<$nb_card; $i++)
{
    echo '<li>';
    echo HtmlInput::card_detail($a_card[$i]['qcode'], $a_card[$i]['name']);
    echo '</li>';
}
echo '</ol>';
echo '<ul class="aligned-block">';
echo '<li>';
echo HtmlInput::button_close("info_card_accounting");
echo '</li>';
echo '</ul>';

echo '</div>';