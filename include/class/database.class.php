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
                pg_lo_unlink($cn, $old_oid);
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


}