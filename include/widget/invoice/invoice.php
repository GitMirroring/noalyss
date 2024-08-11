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
 * \brief display the next invoice to  to be paid or late for customer or supplier
 */
namespace Noalyss\Widget;

class Invoice extends Widget
{
    function input_parameter()
    {
        $tiers = new \ISelect('tiers');
        $tiers->value[] = array('value' => 'S', 'label' => _("Fournisseurs"));
        $tiers->value[] = array('value' => 'C', 'label' => _("Clients"));
        $time_limit = new \ISelect('time_limit');
        $time_limit->value[] = array('value' => 'P', 'label' => _("Prochaines factures"));
        $time_limit->value[] = array('value' => 'R', 'label' => _("Factures en retard"));
        $time_limit->value[] = array('value' => 'T', 'label' => _("Factures pour aujourd'hui"));

        $input = _("Factures ") . $tiers->input() . " " . _("échéance") . " " . $time_limit->input();
        $this->make_form($input);

    }

    function display_parameter()
    {
        $aParam = $this->get_parameter();
        $aTiers = ['S' => _("Fournisseurs"), "C" => _("Clients")];
        $aLimit = ['P' => _("Prochaines"), "R" => "Retard",'T'=>_("Aujourd'hui")];
        echo '<span class="widget_param">'.$aTiers[$aParam['tiers']] . " " . $aLimit[$aParam["time_limit"]].'</span>';
    }

    function display()
    {
        $this->open_div();
        $aParam = $this->get_parameter();
        $aTiers = ['S' => _("Fournisseurs"), "C" => _("Clients")];
        $aLimit = ['P' => _("Prochaines factures"), "R" => "facture en retard",'T'=>_("Aujourd'hui")];
        $title = $aTiers[$aParam['tiers']] . " " . $aLimit[$aParam["time_limit"]];
        echo h2($title, 'class="title"');
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