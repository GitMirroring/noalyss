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
 * \brief display the next invoice to be paid or late for customer or supplier
 */
namespace Noalyss\Widget;
/*!
 * \class
 * \brief display the next invoice to be paid or late for customer or supplier
 */
class Invoice extends Widget
{
    /**
     * @brief return the constant array Tiers
     * @return array
     */
    static function getConstantTiers() : array
    {
        return ['S' => _("Fournisseurs"), "C" => _("Clients")];;
    }
    /**
     * @brief return the constant array Limit
     * @return array
     */
    static function getConstantLimit() :array {
        return  ['P' => _("Prochaines factures"), "R" => "facture en retard",'T'=>_("Aujourd'hui")];
    }

    /**
     * @brief let choice what to display
     * @return void
     */
    function input_parameter()
    {
        $tiers = new \ISelect('tiers');
        $aTiers=Invoice::getConstantTiers();
        $tiers->value=[];
        foreach ($aTiers as $key=>$value) {
            $tiers->value[]=['value'=>$key,'label'=>$value];
        }
        $time_limit = new \ISelect('time_limit');
        $aLimit=Invoice::getConstantLimit();
        $time_limit->value=[];
        foreach ($aLimit as $key=>$value) {
            $time_limit->value[]=['value'=>$key,'label'=>$value];
        }

        $input = _("Factures ") . $tiers->input() . " " . _("échéance") . " " . $time_limit->input();
        $this->make_form($input);

    }

    /**
     * @brief display the parameter
     * @return void
     */
    function display_parameter()
    {
        $aParam = $this->get_parameter();
        $aTiers =Invoice::getConstantTiers();
        $aLimit = Invoice::getConstantLimit();
        echo '<span class="widget_param">'.$aTiers[$aParam['tiers']] . " " . $aLimit[$aParam["time_limit"]].'</span>';
    }

    /**
     * @brief display the widget
     * @return void
     * @throws \Exception
     */
    function display()
    {
        $this->open_div();
        $aParam = $this->get_parameter();
        $aTiers = Invoice::getConstantTiers();
        $aLimit = Invoice::getConstantLimit();
        $title = $aTiers[$aParam['tiers']] . " " . $aLimit[$aParam["time_limit"]];

        $this->title($title);

        $acc_ledger = new \Acc_Ledger($this->db, 0);

        $ledger_type = 'ACH';
        if ($aParam['tiers'] == 'C') {
            $ledger_type = 'VEN';
        }

        switch ($aParam['time_limit']) {
            case 'P':
                $array = $acc_ledger->get_operation_date(date('d.m.Y'), $ledger_type, '>');
                break;
            case 'R':
                $array = $acc_ledger->get_operation_date(date('d.m.Y'), $ledger_type, '<');
                break;
            case 'T':
                $array = $acc_ledger->get_operation_date(date('d.m.Y'), $ledger_type, '=');
                break;
        }
        include "invoice-display.php";
        $this->close_div();


    }
}