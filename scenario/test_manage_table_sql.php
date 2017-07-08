<?php

/*
 *   This file is part of PhpCompta.
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
// Copyright (2016) Author Dany De Bontridder <dany@alchimerys.be>
 ini_set('disable_functions', 'exit,die,header');
 //@description:Test the class manage_table_sql and javascript

 $_GET=array (
  'gDossier' => '10079',
);
$_POST=array (
);
$_POST['gDossier']=$gDossierLogInput;
$_GET['gDossier']=$gDossierLogInput;
$_REQUEST=array_merge($_GET,$_POST);
require_once NOALYSS_INCLUDE."/database/menu_ref_sql.class.php";
require_once NOALYSS_INCLUDE."/lib/manage_table_sql.class.php";

$jrn=new Menu_Ref_SQL($cn);

$manage_table=new Manage_Table_SQL($jrn);
$manage_table->set_callback("ajax_misc.php");
$manage_table->create_js_script();
?>
<script>
    <?php echo $manage_table->get_js_variable()?>.param_add({gDossier:"<?php echo Dossier::id()?>"})
    </script>
<?php

// Test the column header
$manage_table->set_col_label('me_code', "Code");
$manage_table->set_col_label('me_menu', "Menu");
$manage_table->set_col_label('me_file', "Fichier");
$manage_table->set_col_label('me_description', "Description");
$manage_table->set_col_label('me_description_etendue', "Detail");

// Change visible property
$manage_table->set_property_visible("me_javascript", false);
$manage_table->set_property_visible("me_url", false);




// Change update property
$manage_table->set_property_updatable("me_type", false);
$manage_table->set_property_updatable("me_parameter", false);

// Check

echo "me_javascript Visible (false)" .$manage_table->get_property_visible("me_javascript");
if ( ! $manage_table->get_property_visible("me_javascript")) echo "OK"; else echo "FAIL";echo "<br>";
echo "me_javascript updatable (true)" .$manage_table->get_property_updatable("me_javascript");
if (  $manage_table->get_property_updatable("me_javascript")) echo "OK"; else echo "FAIL";echo "<br>";
echo "me_url Visible (false)" .$manage_table->get_property_visible("me_url");
if ( ! $manage_table->get_property_visible("me_url")) echo "OK"; else echo "FAIL";echo "<br>";
echo "me_url updatable (true)" .$manage_table->get_property_updatable("me_url");
if (  $manage_table->get_property_updatable("me_url")) echo "OK"; else echo "FAIL";echo "<br>";

echo "me_type Visible (true)" ;
if (  $manage_table->get_property_visible("me_type")) echo "OK"; else echo "FAIL";echo "<br>";
echo "me_type updatable (false)" ;
if ( ! $manage_table->get_property_updatable("me_type")) echo "OK"; else echo "FAIL";echo "<br>";

echo "me_parameter Visible (true)" ;
if (  $manage_table->get_property_visible("me_parameter")) echo "OK"; else echo "FAIL";echo "<br>";
echo "me_parameter updatable (false)" ;
if ( ! $manage_table->get_property_updatable("me_parameter")) echo "OK"; else echo "FAIL";echo "<br>";

$manage_table->display_table();
 
 ?>
