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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 18/08/24
/*! 
 * \file
 * \brief display events for 10 days
 */
//echo \HtmlInput::title_box(_("A faire"), uniqid(), 'custom', $this->button_zoom(), 'n');
$this->title(_("Ev. pour 14 jours") );
if ( empty ($array)) {
    echo _("Aucun événement en retard ou prévu");
    return;
}
$idx=0;
foreach ($array as $item):
    $idx++;
$class=($idx&1)?'odd':'even';
$style="";
$date=date('ymd');
if ($item['remind_date'] < $date) {
    $style="background-color:brown;color:white;";
} elseif ($item['remind_date'] == $date ) {
    $style="background-color:orange;color:white;";
}
?>
<div class="row <?=$class?> border" style="<?=$style?>">
    <div class="col-2">
        <?=$item['ag_timestamp_fmt']?>
    </div>
    <div class="col" style="text-overflow:hidden;text-wrap:nowrap;text-overflow:ellipsis ">
        <?=HtmlInput::detail_action($item['ag_id'],
        $item['ag_ref']." ".$item['ag_title'])?>
    </div>
</div>
<?php
endforeach;
?>


