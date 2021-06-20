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

/*!\file
 * \brief definition of the class Pre_op_ods
 */

/*---------------------------------------------------------------------- */
/*!\brief concerns the predefined operation for ODS ledger
*/
class Pre_op_ods extends Pre_operation_detail
{
    var $op;
    function __construct($cn)
    {
        parent::__construct($cn);
    }

    /***
     * @brief retrieve data from $_POST
     */
    function get_post()
    {
        $http=new \HttpInput();
        $nb=$http->post("nb_item","number");
        for ($i=0;$i< $nb ;$i++)
        {
            $this->{"e_account".$i}=$http->post("e_account'.$i");
            $this->{"e_account".$i."_amount"}=$http->post("e_account".$i."_amount","number",0);
            $this->{"e_account".$i."_type"}=$http->post('e_account'.$i."_type");

        }
    }
    /*!
     * \brief save the detail and op in the database
     *
     */
    function save($p_od_id,$p_nbitem)
    {
        try
        {
            // save the selling
            for ($i=0;$i< $p_nbitem ;$i++)
            {
                $sql='insert into op_predef_detail (opd_poste,opd_amount,'.
                             'opd_debit,od_id)'.
                             ' values ($1,$2,$3,$4)';

                $this->db->exec_sql($sql,array($this->{"e_account".$i},
                                                $this->{"e_account".$i."_amount"},
                                                ($this->{"e_account".$i."_type"}=='d')?'t':'f',
                                                $p_od_id)
                                    );
            }
        }
        catch (Exception $e)
        {
            record_log($e);
            echo ($e->getMessage());
            throw $e;
        }

    }
    /*!\brief compute an array accordingly with the FormVenView function
     */
    function compute_array($p_od_id)
    {
        $count=0;
        $p_array=$this->load($p_od_id);
        foreach ($p_array as $row)
        {
            $c=($row['opd_debit']=='t')?'d':'c';
            $array+=array("e_account".$count=>$row['opd_poste'],
                          "e_account".$count."_amount"=>$row['opd_amount'],
                          "e_account".$count."_type"=>$c
                         );
            $count++;

        }
        return $array;
    }
    /*!\brief load the data from the database and return an array
     * \return an array 
     */
    function load($p_od_id)
    {
        $sql="select opd_id,opd_poste,opd_amount,opd_debit".
             "  from op_predef_detail where od_id= $1 ".
             " order by opd_debit, opd_id,opd_amount";
        $res=$this->db->exec_sql($sql,array($p_od_id));
        $array=Database::fetch_all($res);
        return $array;
    }
}
