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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 18/08/24
/*! 
 * \file
 * \brief show 10 next events , or 10 late
 */

namespace Noalyss\Widget;

/*!
 * \class Event
 * \brief show 10 next events , or 10 late
*/

class Event extends Widget
{
    /**
     * @brief get the action where the remind day is within 14 days
     * @return array
     */
    function get_next10()
    {
        $sql="select ag_ref
        ,ag_hour
        ,coalesce(vw_name,'Interne') as vw_name
        ,coalesce(quick_code,'interne') as quick_code
        ,ag_id
        ,ag_title
        ,ag_ref
        , dt_value
        ,to_char(ag_remind_date,'DD.MM.YY') as ag_timestamp_fmt
        ,ag_timestamp 
        ,to_char(ag_remind_date,'YYMMDD') as remind_date 
        from action_gestion join document_type 
             on (ag_type=dt_id) 
                  left join vw_fiche_attr on (f_id=f_id_dest) 
                  where 
                  ag_state not in (1,4)
                  and to_char(ag_remind_date,'YYYYMMDD')<=to_char(now()+interval '14 days','YYYYMMDD')
                  and ". \Follow_Up::sql_security_filter($this->db,'R')
        ." order by ag_remind_date asc";
        $array=$this->db->get_array($sql);
        return $array;
    }

    function display()
    {

       $this->open_div();
       $array=$this->get_next10();
       require "event-display.php";

       $this->close_div();
    }
}
