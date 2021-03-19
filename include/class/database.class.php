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
 * \file
 * \brief contains the class for connecting to Noalyss
 */

require_once NOALYSS_INCLUDE . '/lib/database_core.class.php';

class Database extends DatabaseCore
{
    /**\brief constructor
     * \param $p_database_id is the id of the dossier, or the modele following the
     * p_type if = 0 then connect to the repository
     * \param $p_type is 'DOS' (defaut) for dossier or 'MOD'
     */

    function __construct($p_database_id = 0, $p_type = 'dos')
    {
        if (IsNumber($p_database_id) == false || strlen($p_database_id) > 10)
            die("-->Dossier invalide [$p_database_id]");
        $noalyss_user = (defined("noalyss_user")) ? noalyss_user : phpcompta_user;
        $password = (defined("noalyss_password")) ? noalyss_password : phpcompta_password;
        $port = (defined("noalyss_psql_port")) ? noalyss_psql_port : phpcompta_psql_port;
        $host = (!defined("noalyss_psql_host")) ? '127.0.0.1' : noalyss_psql_host;
        if (defined("MULTI") && MULTI == "0") {
            $l_dossier = dbname;
        } else {

            if ($p_database_id == 0) { /* connect to the repository */
                $l_dossier = sprintf("%saccount_repository", strtolower(domaine));
            } else if ($p_type == 'dos') { /* connect to a folder (dossier) */
                $l_dossier = sprintf("%sdossier%d", strtolower(domaine), $p_database_id);
            } else if ($p_type == 'mod') { /* connect to a template (modele) */
                $l_dossier = sprintf("%smod%d", strtolower(domaine), $p_database_id);
            } else if ($p_type == 'template') {
                $l_dossier = 'template1';
            } else {
                throw new Exception('Connection invalide');
            }
        }

        parent::__construct($noalyss_user, $password, $l_dossier, $host, $port);

        if ($this->exist_schema('comptaproc')) {
            $this->exec_sql('set search_path to public,comptaproc,pg_catalog;');
        }
        $this->exec_sql('set DateStyle to ISO, MDY;');

    }

    /***
     * \brief Save a "piece justificative" , the name must be pj
     *
     * \param $seq jr_grpt_id
     * \return $oid of the lob file if success
     *         null if a error occurs
     *
     */
    function save_receipt($seq)
    {
        $oid = $this->upload('pj');
        if ($oid == false) {
            return false;
        }
        // Remove old document
        $ret = $this->exec_sql("select jr_pj from jrn where jr_grpt_id=$seq");
        if (pg_num_rows($ret) != 0) {
            $r = pg_fetch_array($ret, 0);
            $old_oid = $r['jr_pj'];
            if (strlen($old_oid) != 0)
                $this->lo_unlink( $old_oid);
        }
        // Load new document
        $this->exec_sql("update jrn set jr_pj=$1 , jr_pj_name=$2,
                                jr_pj_type=$3  where jr_grpt_id=$4",
            array($oid, $_FILES['pj']['name'], $_FILES['pj']['type'], $seq));
        return $oid;
    }

    /**
     * \brief Get version of a database, the content of the
     *        table version
     *
     * \return version number
     *
     */

    function get_version()
    {
        $Res = $this->get_value("select max(val) from version");
        return $Res;
    }

    /**
     * \brief loop to apply all the path to a folder or     a template
     * Upgrade check if the folder $p_name needs to be upgrade thanks the variable DBVERSION
     * and run  all the SQL script named  upgradeX.sql from the folder noalyss/include/sql/patch
     * until  X equal DBVERSION-1
     *
     * \param $p_name database name
     *
     */

    function apply_patch($p_name)
    {
        if (!$this->exist_table('version')) {
            echo _('Base de donnée vide');
            return;
        }
        $MaxVersion = DBVERSION - 1;
        $succeed = "<span style=\"font-size:18px;color:green\">&#x2713;</span>";
        echo '<ul style="list-type-style:square">';
        for ($i = 4; $i <= $MaxVersion; $i++) {
            $to = $i + 1;
            if ($this->get_version() <= $i) {
                if ($this->get_version() == 97) {
                    if ($this->exist_schema("amortissement")) {
                        $this->exec_sql('ALTER TABLE amortissement.amortissement_histo
							ADD CONSTRAINT internal_fk FOREIGN KEY (jr_internal) REFERENCES jrn (jr_internal)
							ON UPDATE CASCADE ON DELETE SET NULL');
                    }
                }
                echo "<li>Patching " . $p_name .
                    " from the version " . $this->get_version() . " to $to ";

               $this->execute_script(NOALYSS_INCLUDE . '/sql/patch/upgrade' . $i . '.sql');
               echo $succeed;

                if ( DEBUGNOALYSS == 0)
                    ob_start();
                // specific for version 4
                if ($i == 4) {
                    $sql = "select jrn_def_id from jrn_def ";
                    $Res = $this->exec_sql($sql);
                    $Max = $this->size();
                    for ($seq = 0; $seq < $Max; $seq++) {
                        $row = pg_fetch_array($Res, $seq);
                        $sql = sprintf("create sequence s_jrn_%d", $row['jrn_def_id']);
                        $this->exec_sql($sql);
                    }
                }
                // specific to version 7
                if ($i == 7) {
                    // now we use sequence instead of computing a max
                    //
                    $Res2 = $this->exec_sql('select coalesce(max(jr_grpt_id),1) as l from jrn');
                    $Max2 = pg_NumRows($Res2);
                    if ($Max2 == 1) {
                        $Row = pg_fetch_array($Res2, 0);
                        var_dump($Row);
                        $M = $Row['l'];
                        $this->exec_sql("select setval('s_grpt',$M,true)");
                    }
                }
                // specific to version 17
                if ($i == 17) {
                    $this->execute_script(NOALYSS_INCLUDE . '/sql/patch/upgrade17.sql');
                    $max = $this->get_value('select last_value from s_jnt_fic_att_value');
                    $this->alter_seq($p_cn, 's_jnt_fic_att_value', $max + 1);
                } // version
                // reset sequence in the modele
                //--
                if ($i == 30 && $p_name == "mod") {
                    $a_seq = array('s_jrn', 's_jrn_op', 's_centralized',
                        's_stock_goods', 'c_order', 's_central');
                    foreach ($a_seq as $seq) {
                        $sql = sprintf("select setval('%s',1,false)", $seq);
                        $Res = $this->exec_sql($sql);
                    }
                    $sql = "select jrn_def_id from jrn_def ";
                    $Res = $this->exec_sql($sql);
                    $Max = pg_NumRows($Res);
                    for ($seq = 0; $seq < $Max; $seq++) {
                        $row = pg_fetch_array($Res, $seq);
                        $sql = sprintf("select setval('s_jrn_%d',1,false)", $row['jrn_def_id']);
                        $this->exec_sql($sql);
                    }
                }
                if ($i == 36) {
                    /* check the country and apply the path */
                    $res = $this->exec_sql("select pr_value from parameter where pr_id='MY_COUNTRY'");
                    $country = pg_fetch_result($res, 0, 0);
                    $this->execute_script(NOALYSS_INCLUDE . "/sql/patch/upgrade36." . $country . ".sql");
                    $this->exec_sql('update tmp_pcmn set pcm_type=find_pcm_type(pcm_val)');
                }
                if ($i == 59) {
                    $res = $this->exec_sql("select pr_value from parameter where pr_id='MY_COUNTRY'");
                    $country = pg_fetch_result($res, 0, 0);
                    if ($country == 'BE')
                        $this->exec_sql("insert into parm_code values ('SUPPLIER',440,'Poste par défaut pour les fournisseurs')");
                    if ($country == 'FR')
                        $this->exec_sql("insert into parm_code values ('SUPPLIER',400,'Poste par défaut pour les fournisseurs')");
                }
                if ($i == 61) {
                    $country = $this->get_value("select pr_value from parameter where pr_id='MY_COUNTRY'");
                    $this->execute_script(NOALYSS_INCLUDE . "/sql/patch/upgrade61." . $country . ".sql");
                }
                /**
                 * Bug in parm_code for FRANCE
                 */
                if ($i == 141 ) {
                     $country = $this->get_value("select pr_value from parameter where pr_id='MY_COUNTRY'");
                     $this->execute_script(NOALYSS_INCLUDE . "/sql/patch/upgrade141." . $country . ".sql");
                }

                if (DEBUGNOALYSS == 0 )
                    ob_end_clean();

            }
        }
        echo '</ul>';
    }

    /**
     * return the name of the database with the domain name
     * @param $p_id of the folder WITHOUT the domain name
     * @param $p_type dos for folder mod for template
     * @return formatted name
     */
    function format_name($p_id, $p_type)
    {
        switch ($p_type) {
            case 'dos':
                $sys_name = sprintf("%sdossier%d", strtolower(domaine), $p_id);
                break;
            case 'mod':
                $sys_name = sprintf("%smod%d", strtolower(domaine), $p_id);
                break;
            default:
                echo_error(__FILE__ . " format_name invalid type " . $p_type, __LINE__);
                throw new Exception(__FILE__ . " format_name invalid type " . $p_type . __LINE__);
        }
        return $sys_name;
    }
}
