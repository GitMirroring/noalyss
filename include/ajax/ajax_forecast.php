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

// Copyright Author Dany De Bontridder danydb@noalyss.eu

require_once NOALYSS_INCLUDE . "/database/forecast_sql.class.php";

$http = new HttpInput();
$input = $http->request("input");
$action = $http->request("ieaction", "string", "display");

if ($action == "display") {
    $ajax = Inplace_Edit::build($input);
    $f_id = $http->request("f_id", "number");

    $ajax->set_callback("ajax_misc.php");
    $ajax->add_json_param("op", "forecast");
    $ajax->add_json_param("gDossier", Dossier::id());
    $ajax->add_json_param("f_id", $f_id);
    echo $ajax->ajax_input();
}
if ($action == "ok") {
    $f_id = $http->request("f_id", "number");
    $ajax = Inplace_Edit::build($input);
    $ajax->set_callback("ajax_misc.php");
    $ajax->add_json_param("op", "forecast");
    $ajax->add_json_param("gDossier", Dossier::id());
    $ajax->add_json_param("f_id", $f_id);

    $value = $http->request("value");
    $ajax->set_value($value);
    $input = $ajax->get_input();

    $data_sql = new Forecast_SQL($cn, $f_id);
    switch ($input->name) {
        case 'f_name':
            $data_sql->setp($input->name, $value);
            break;
        case 'p_start':
            $data_sql->setp("f_start_date", $value);
            break;
        case 'p_end':
            $data_sql->setp("f_end_date", $value);
            break;
    }

    $data_sql->update();
    echo $ajax->value();
}
if ($action == "cancel") {
    $ajax = Inplace_Edit::build($input);
    $f_id = $http->request("f_id", "number");

    $ajax->set_callback("ajax_misc.php");
    $ajax->add_json_param("op", "forecast");
    $ajax->add_json_param("gDossier", Dossier::id());
    $f_id = $http->request("f_id", "number");
    $ajax->add_json_param("f_id", $f_id);

    echo $ajax->value();
}
