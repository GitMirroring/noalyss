<?php
/*
 * * Copyright (C) 2015 Dany De Bontridder <dany@alchimerys.be>
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

 *
 */


/**
 * @file
 * @brief  display a list of filter for followup
 */
global $cn;
$http = new HttpInput();
$div = $http->request("ctl");
$acces_code=$http->get("ac");
echo \HtmlInput::title_box("liste ",$div);
$dossier_id = Dossier::id();
$a_list = $cn->get_array("select af_id, af_name,af_search 
from action_gestion_filter 
where af_user=$1 
order by lower(af_name)", [$login]);
if (count($a_list) > 0) :


?>
<p>
    Cliquer sur un filtre pour chercher
</p>
<?php
echo \HtmlInput::filter_list("filter_list_ul");

?>


<ul id="filter_list_ul" class="list-group m-2">

<?php
foreach ($a_list as $item) :
    $array = json_decode($item['af_search']);
    $url = "do.php?" . http_build_query($array);

?>

<li id="item_fu<?=$item['af_id']?>" class="list-group-item-action" style="background-color: transparent">
    <a href="<?=$url?>" class="line">
    <span class="search-content">
        <?php
        echo h($item['af_name']);
        ?>

    </span>
    </a>
        <?php
        echo \Icon_Action::trash(uniqid(), sprintf("delete_filter_followup('%s','%s')",
            $dossier_id, $item['af_id']));
        ?>
    <?php
    endforeach;
    ?>

</ul>
<?php
else:
    echo span("Aucun recherche sauvée",'class="notice"');

endif;

?>
<ul  class="list-group m-2">

    <li class="list-group-item-action">
        <?php
        $url = "do.php?".http_build_query(["ac"=> $acces_code,"gDossier"=>$dossier_id]);
        ?>
        <a href="<?=$url?>" >
            Aucun filtre
        </a>
    </li>
</ul>

<ul class="aligned-block">
    <li>
<?php
echo \HtmlInput::button_close("$div");
?>
    </li>
</ul>
