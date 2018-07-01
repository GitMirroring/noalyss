<?php
//@description:Test of Html_Tab and Output_Html_Tab
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
// Copyright (2018) Author Dany De Bontridder <dany@alchimerys.be>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief  Test of Html_Tab and Output_Html_Tab
 */
require_once NOALYSS_INCLUDE.'/lib/html_tab.class.php';
require_once NOALYSS_INCLUDE.'/lib/output_html_tab.class.php';

$tab = new Html_Tab('tab1',_("Titre 1"));
$tab->set_mode('link');
$tab->set_link(http_build_query([ "a"=>1,"b"=>2]));
$tab2 = new Html_Tab('tab2',_("Titre 2"));
$tab2->set_content(""
        . "<br> Très longue chaine HTML pour 2"
        . "<br> Très longue chaine HTML pour 2"
        . "<br> Très longue chaine HTML pour 2"
        . "<br> Très longue chaine HTML pour 2"
        . "<br> Très longue chaine HTML pour 2"
        . "<br> Très longue chaine HTML pour 2"
        . "<br> Très longue chaine HTML pour 2"
        . "<br> Très longue chaine HTML pour 2"
        . "<br> Très longue chaine HTML pour 2"
        . "<br> Très longue chaine HTML pour 2"
        . "<br> Très longue chaine HTML pour 2"
        . "<br> Très longue chaine HTML pour 2");
$tab3 = new Html_Tab('tab3',_("Titre 3"));
$tab3->set_content('<p >'
        . "<br> Très longue chaine HTML pour 3"
        . "<br> Très longue chaine HTML pour 3"
        . "<br> Très longue chaine HTML pour 3"
        . "<br> Très longue chaine HTML pour 3"
        . "<br> Très longue chaine HTML pour 3"
        . "<br> Très longue chaine HTML pour 3"
        . "<br> Très longue chaine HTML pour 3"
        . "<br> Très longue chaine HTML pour 3"
        . "<br> Très longue chaine HTML pour 3"
        . "<br> Très longue chaine HTML pour 3"
        . "<br> Très longue chaine HTML pour 3"
        . "<br> Très longue chaine HTML pour 3"
        . "<br> Très longue chaine HTML pour 3"
        . "<br> Très longue chaine HTML pour 3"
        .'</p>'
        );

$output = new Output_Html_Tab; 

$output->add($tab);
$output->add($tab2);
$output->add($tab3);


$output->output();
