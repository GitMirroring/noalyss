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
// Copyright (2002-2020) Author Dany De Bontridder <danydb@noalyss.eu>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief  display the options of a card
 */
require_once NOALYSS_INCLUDE."/class/card_multiple.class.php";

$card_multiple=new Card_Multiple();
try
{
    $ap_id=$http->get("ap_id");
}
catch (Exception $exc)
{
    echo $exc->getMessage();
    record_log($exc->getMessage().$exc->getTraceAsString());
}

echo HtmlInput::title_box(_("Options contact"), "d_linked_card_option","close","","y");
// display existing option
$card_multiple->display_option($ap_id);
