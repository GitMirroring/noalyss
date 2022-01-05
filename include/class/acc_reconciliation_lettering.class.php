<?php

/*
 * Copyright (C) 2022 Dany De Bontridder <dany@alchimerys.be>
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
 */


/***
 * @file 
 * @brief this class let you insert reconcilied operation  from Lettering::save without calling auto_letter
 *
 */

/***
 * @class
 * @brief this class let you insert reconcilied operation  from Lettering::save without calling auto_letter
 *
 */
class Acc_Reconciliation_Lettering extends Acc_Reconciliation
{
    const sql_find_operation="select jr_id from jrn join jrnx on (jr_grpt_id=j_grpt) where j_id=$1";
    /**
     * @brief do nothing , it let you skip Acc_Reconcililation
     * @param type $p_jr_id
     * @return type
     */
    public function auto_letter($p_jr_id)
    {
        return ;
    }
    /**
     * @brief find the jrn.jr_id and call the Acc_Reconciliation::insert_rapt , the auto_letter will be skipped
     * @param int $p_jrnx_id jrnx.j_id row id to reconcield
     * @param int $p_jrnx_id_rec jrnx.j_id row id to reconcield
     */
    public function insert_reconcilied(int $p_jrnx_id,int $p_jrnx_id_rec) 
    {
        // find jr_id from both
        $jrn_id = $this->db->get_value(self::sql_find_operation,
                [$p_jrnx_id]);
        $jrn_id2 = $this->db->get_value(self::sql_find_operation,[$p_jrnx_id_rec]);
        $this->set_jr_id($jrn_id);
        return $this->insert_rapt($jrn_id2);
    }
}
