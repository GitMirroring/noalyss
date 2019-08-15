<?php

/*
 * Copyright (C) 2019 Dany De Bontridder <dany@alchimerys.be>
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
 * @brief contains prepared query used in different classes of the application
 * 
 *
 */
class Prepared_Query {
    private $db; //!< Database connection
    
    function __construct(Database $cn)
    {
        $this->db=$cn;
    }

    public function get_db()
    {
        return $this->db;
    }

    public function set_db($db)
    {
        $this->db=$db;
        return $this;
    }

    /**
     * Prepare the query for fetching the linked operation. ONE paramete is needed : jrn.jr_id , returns all columns 
     * from  the table PUBLIC.JRN, column qcode_bank (default card for fin ledger) of the operation concerning
     * to the payment
     * 
     * @see export_ledger_pdf.php
     * @see Acc_Ledger_History
     * 
     */
    public function prepare_reconcile_date()
    {
        $prepare=$this->db->is_prepare("reconcile_date");
        if ($prepare==FALSE)
        {
            $this->db->prepare('reconcile_date',
                    'select  * 
                        , (select ad_value from fiche_detail where ad_id=23 and f_id=jrn_def.jrn_def_bank ) as qcode_bank
                         from 
                           jrn 
                           join jrn_def on (jrn.jr_def_id=jrn_def.jrn_def_id)
                         where 
                           jr_id in 
                               (select 
                                   jra_concerned 
                                   from 
                                   jrn_rapt 
                                   where jr_id = $1 
                                union all 
                                select 
                                jr_id 
                                from jrn_rapt 
                                where jra_concerned=$1)');
        }
    }
    
    
}
