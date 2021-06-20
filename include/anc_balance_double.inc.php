<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
/**
 *@file
 *@brief Print the crossed balance between 2 plan
 *@see Anc_Balance_Double
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
$bc = new Anc_Balance_Double($cn);
$bc->get_request();
echo '<form method="get">';
echo $bc->display_form();
echo '</form>';
if (isset($_GET['result']))
{
    try {
        $result = $bc->display_html();
        if ($bc->has_data > 0) {
            echo $bc->show_button();
            echo $result;
        }else
        {
            echo '<p class="notice">';
            echo _('Aucune donnée trouvée');
            echo '</p>';
        }

    } catch (Exception $e){
        alert($e->getMessage());
    }
}

?>
