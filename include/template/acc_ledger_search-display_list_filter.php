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

/*!
* \file
* \brief display a list of saved search for accountancy (alias filter)
*/
global $g_user;
$array=$this->cn->get_array("select id,filter_name,description,ledger_type
from public.user_filter where login=$1 and ledger_type=$2",
[$g_user->getLogin(),$this->type]);
$nb_array=count($array);
$http=new HttpInput();
$div="search_op";
?>
<p>
    Cliquez sur un lien pour affiche le résultat de la recherche
</p>

<?php
echo \HtmlInput::filter_list("filter_list_acc_ul");

?>
<ul  id="filter_list_acc_ul" class="list-group m-2" >

<?php
for ($i=0;$i<$nb_array;$i++):
?>
<li class="list-group-item-action" style="background-color: transparent">
    <?php
    $user_filter=new \User_filter_SQL($this->cn,$array[$i]["id"]);

    $a_param=array();
    $a_param["search_opnb_jrn"] = $user_filter->getp("nb_jrn");
    if ($a_param["search_opnb_jrn"] > 0 ) {
        $a_param['search_opr_jrn']=explode(",",$user_filter->getp("r_jrn"));
    }
    $a_param    ["date_start"] =  $user_filter->getp("date_start");
    $a_param    ["date_end"] =  $user_filter->getp("date_end");
    $a_param    ["date_paid_start"] =  $user_filter->getp("date_paid_start");
    $a_param    ["date_paid_end"] =  $user_filter->getp("date_paid_end");
    /*[search_opdate_start_hidden] => 01.01.2023
    [search_opdate_end_hidden] => 31.12.2023*/
    $a_param["desc"] =$user_filter->getp("description");
    $a_param["amount_min"] = $user_filter->getp("amount_min");
    $a_param["amount_max"] = $user_filter->getp("amount_max");
    $a_param["search_opqcode"] =$user_filter->getp("qcode");
    $a_param["accounting"] = $user_filter->getp("accounting");
    $a_param["tva_id_search"] = $user_filter->getp("tva_id_search");
    $a_param["operation_filter"] = $user_filter->getp("operation_filter");
    $a_param["p_currency_code"] = $user_filter->getp("uf_currency_code");
    $a_param["search_optag_option"] =$user_filter->getp("uf_tag_option");
    if ( $user_filter->getp("uf_tag") != "") {
        $a_param["search_optag"]=explode(",",$user_filter->getp("uf_tag"));
    }
    $a_param['search_opqcode']=$user_filter->getp('qcode');
    $a_param['ledger_type']=$array[$i]['ledger_type'];

    $a_param['ac']=$http->request("ac");
    $a_param['gDossier']=$http->request("gDossier","number");
    $url="do.php?".http_build_query($a_param);
    ?>
    <a class="line" href="<?=$url?>">
    <span class="search-content">
    <?=$array[$i]['filter_name']?>  <?=h($array[$i]['description'])?>

    </span>
    </a>
</li>
<?php
endfor;
?>
</ul>
    <ul  class="list-group m-2" >
    <li class="list-group-item-action" style="background-color: transparent">
    <?php
    $a_param=array();
    $a_param['ac']=$http->request("ac");
    $a_param['gDossier']=$http->request("gDossier","number");
    $url="do.php?".http_build_query($a_param);
    ?>
    <a class="line" href="<?=$url?>">
       Aucun filtre
    </a>
    </li>

</ul>
