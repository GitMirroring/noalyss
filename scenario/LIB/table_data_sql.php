<?php
//@description: test Table_Data_SQL and Data_SQL for virtual columns

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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 22/10/23


/**
 * @file
 * @brief Test Table_Data_SQL
 */

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

class FakeData2_SQL extends \Table_Data_SQL {

    function __construct(Database $p_cn, $p_id = -1) {
        $this->table = "public.jrn";
        $this->primary_key = "jr_id";
        /*
         * List of columns
         */
        $this->name = array(
            "jr_id" => "jr_id"
            , "jr_def_id" => "jr_def_id"
            , "jr_montant" => "jr_montant"
            , "jr_comment" => "jr_comment"
            , "jr_date" => "jr_date"
            , "jr_grpt_id" => "jr_grpt_id"
            , "jr_internal" => "jr_internal"
            , "jr_tech_date" => "jr_tech_date"
            , "jr_tech_per" => "jr_tech_per"
            , "jrn_ech" => "jrn_ech"
            , "jr_ech" => "jr_ech"
            , "jr_rapt" => "jr_rapt"
            , "jr_valid" => "jr_valid"
            , "jr_opid" => "jr_opid"
            , "jr_c_opid" => "jr_c_opid"
            , "jr_pj" => "jr_pj"
            , "jr_pj_name" => "jr_pj_name"
            , "jr_pj_type" => "jr_pj_type"
            , "jr_pj_number" => "jr_pj_number"
            , "jr_mt" => "jr_mt"
            , "jr_date_paid" => "jr_date_paid"
            , "jr_optype" => "jr_optype"
            , "currency_id" => "currency_id"
            , "currency_rate" => "currency_rate"
            , "currency_rate_ref" => "currency_rate_ref"
        );

        /*
         * Type of columns
         */
        $this->type = array(
            "jr_id" => "numeric"
            , "jr_def_id" => "numeric"
            , "jr_montant" => "numeric"
            , "jr_comment" => "text"
            , "jr_date" => "date"
            , "jr_grpt_id" => "numeric"
            , "jr_internal" => "text"
            , "jr_tech_date" => "date"
            , "jr_tech_per" => "numeric"
            , "jrn_ech" => "date"
            , "jr_ech" => "date"
            , "jr_rapt" => "text"
            , "jr_valid" => "boolean"
            , "jr_opid" => "numeric"
            , "jr_c_opid" => "numeric"
            , "jr_pj" => "oid"
            , "jr_pj_name" => "text"
            , "jr_pj_type" => "text"
            , "jr_pj_number" => "text"
            , "jr_mt" => "text"
            , "jr_date_paid" => "date"
            , "jr_optype" => "text"
            , "currency_id" => "numeric"
            , "currency_rate" => "numeric"
            , "currency_rate_ref" => "numeric"
        );

        $this->default = array("jr_id" => "auto");

        $this->date_format = "DD.MM.YYYY";
        parent::__construct($p_cn, $p_id);
    }
}

 global $g_connection;
 $g_connection=$cn;
 echo h1("Ajout une colonne virtuelle ");
$jrn=new FakeData2_SQL($g_connection,911);
$jrn->set_virtual_col("str_date", " to_char(jr_tech_date,'DD/MM/YY HH24:MI')");
$jrn->load();
echo '<pre>'.'$jrn->set_virtual_col("str_date", " to_char(jr_tech_date,\'DD/MM/YY HH24:MI\')");'.'</pre>';
echo p( 'jrn->str_date '.$jrn->str_date);
echo p( '$jrn->get("str_date") '.$jrn->get("str_date"));
echo p( '$jrn->getp("str_date")'.$jrn->getp("str_date"));

echo h1("to_array");
echo '<pre>';
var_dump($jrn->to_array());
echo '</pre>';
$a_result=$jrn->to_array();
echo h1("to_row ");
$a_result['str_date']=" CHANGED ";
$jrn->to_row($a_result);
if ( $jrn->str_date != ' CHANGED ' ) {    
    echo_warning("erreur changement non pris en compte");}
else {
    echo p('Changement Ok');
}
$jrn->update();
$jrn->load();
if ( $jrn->str_date != '05/01/22 00:00' ) {    
    echo_warning("erreur changement non pris en compte");
    var_dump($jrn);
}
else {
    echo p('Ok : update and load');
}

$jrn->to_row(['str_date'=>"Autre date"]);
var_dump($jrn);
echo h1("Donnée inexistante ");
$jrn=new FakeData2_SQL($g_connection,22911);
print $jrn;

echo h1("collect objects");
$jrn_collect = new FakeData2_SQL($g_connection);
$jrn_collect->set_virtual_col("str_date", " to_char(jr_tech_date,'DD/MM/YY HH24:MI')");
$a_jrn = $jrn_collect->collect_objects(" where jr_montant > 0 order by jr_montant desc ");
$nb_jrn = count($a_jrn);
for ($i = 0 ;$i < 20 && $i < $nb_jrn ; $i++ ) {
    echo p($a_jrn[$i]->jr_tech_date." = ".$a_jrn[$i]->str_date);
}
