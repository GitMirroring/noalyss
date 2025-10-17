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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 19/11/22
//@description:Test the textarea , especially the Enrich function
/*! 
 * \file
 * \brief Test the textarea , especially the Enrich function
 */
$http=new HttpInput();
echo '<div class="nicEdit-main">';
echo $http->post("enrich","raw","");
echo $http->post("enrich2","text","");
echo $http->post("enrich3","raw","");
print_r($_POST);
echo '</div>';

?>

<h1>Format Area</h1>
<form method="post">
<?php
$itext=new \ITextarea("enrich");
$itext->value= $http->post("enrich","raw","");

$itext->set_enrichText("full");
echo $itext->input();
?>


<hr>
<h1>Minimum Area</h1>
    <?php
    $itext=new \ITextarea("enrich2");
    $itext->value= $http->post("enrich2","raw","");

    $itext->set_enrichText("minimal");
    echo $itext->input();
    ?>


<p>
    <h1>No Toolbar</h1>
    <?php
    $itext=new \ITextarea("enrich3");
    $itext->value= $http->post("enrich3","raw","");

    $itext->set_enrichText("no-toolbar");
    echo $itext->input();
    ?>


<p>
    
<h1>Plain Area</h1>
    <?php
    $itext=new \ITextarea("enrich2");
    $itext->value= strip_tags($http->post("enrich2","raw",""));

    $itext->set_enrichText("plain");
    echo $itext->input();
    ?>


<p>

    <input type="submit" value="Valider">
</p>

</form>
