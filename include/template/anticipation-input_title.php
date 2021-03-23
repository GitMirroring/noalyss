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

// Copyright Author Dany De Bontridder danydb@aevalys.eu

/**
 * @file
 * @brief  Display a form for the title of a forecast
 *
 */
$forecast_sql = new Forecast_SQL($this->cn, $this->forecast_id);
$fc_name = new IText("f_name", $forecast_sql->getp("f_name"));
$fc_name->id="fc_name";
$is_start = new ISelect("p_start");
$is_start->value = $this->cn->make_array("select p_id,p_start from parm_periode order by p_start");
$is_end = new ISelect("p_end");
$is_end->value = $this->cn->make_array("select p_id,p_end from parm_periode order by p_end");

$is_start->selected = $forecast_sql->getp("f_start_date");
$is_end->selected = $forecast_sql->getp("f_end_date");
$is_start->id=uniqid("sel");
$is_end->id=uniqid("sel");


$ip_name = new Inplace_Edit($fc_name);
$ip_start = new Inplace_Edit($is_start);
$ip_end = new Inplace_Edit($is_end);

$ip_name->add_json_param("op", "forecast");
$ip_start->add_json_param("op", "forecast");
$ip_end->add_json_param("op", "forecast");

$ip_name->add_json_param("gDossier", Dossier::id());
$ip_start->add_json_param("gDossier", Dossier::id());
$ip_end->add_json_param("gDossier", Dossier::id());

$ip_name->add_json_param("f_id", $this->forecast_id);
$ip_start->add_json_param("f_id", $this->forecast_id);
$ip_end->add_json_param("f_id", $this->forecast_id);

$ip_name->set_callback("ajax_misc.php");
$ip_start->set_callback("ajax_misc.php");
$ip_end->set_callback("ajax_misc.php");

?>
<h1>
    <?php
    echo $ip_name->input();
    ?>
</h1>
<div class="form-group">

<label for="p_start" class="form-group"><?= _("Début") ?></label>
<?= $ip_start->input(); ?>
<label for="p_end" class="form-group"><?= _("Fin") ?></label>
<?= $ip_end->input() ?>

</div>
