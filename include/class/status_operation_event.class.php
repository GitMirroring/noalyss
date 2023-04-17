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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 14/04/23

/*! 
 * \file
 * \brief concern the detail in the dashboard, the status of the operations of sales or purchase or event
 */

class Status_Operation_Event
{
    private $cn;
    private $dialog_box_id;

    public function __construct(Database $cn)
    {
        $this->cn=$cn;
        $this->dialog_box_id="situation_detail_div";
    }

    public function __toString(): string
    {
       return "status_operation";
    }


    function display_event($p_title,$p_array,$p_what)
    {
        require_once NOALYSS_TEMPLATE."/status_operation_event-display_event.php";
    }
    /**
     * @brief Display a box with the contains
     * @param array $p_array Data to display
     * @param string $p_title Title of the box
     * @param div_name $p_div id of the box
     */
    function display_operation($p_title,$p_array,$p_what)
    {
        require_once NOALYSS_TEMPLATE."/status_operation_event-display_operation.php";
    }

    /**
     * @brief display what it is asked
     * @param string $p_what
     */
    function display($p_what) {
        $Ledger=new Acc_Ledger($this->cn,0);
        $last_ledger=array();
        $last_ledger=$Ledger->get_last(20);

        switch ($p_what) {
            case 'supplier_now':
                $this->display_operation(_("Fournisseurs à payer aujourd'hui"),$Ledger->get_supplier_now(),$p_what);
                break;
            case 'supplier_late':
                $this->display_operation(_("Fournisseurs en retard"),$Ledger->get_supplier_late(),$p_what);
                break;
            case 'customer_now':
                $this->display_operation(_("Client à payer aujourd'hui"),$Ledger->get_customer_now(),$p_what);
                break;
            case 'customer_late':
                $this->display_operation(_("Client en retard"),$Ledger->get_customer_late(), $p_what);
                break;
            case 'action_now':
                $Operation=new Follow_Up($this->cn);
                $this->display_event(_("Action aujourd'hui"),$Operation->get_today(),$p_what);
            case 'action_late':
                $Operation=new Follow_Up($this->cn);
                $this->display_event(_("Action en retard"),$Operation->get_late(),$p_what);
                break;
            default:
                throw new \Exception("Unknown operation [$p_what]",EXC_PARAM_VALUE);
        }
    }
    static function main_display(Database $cn)
    {

        require_once NOALYSS_TEMPLATE."/status_operation_event-main_display.php";
    }
}