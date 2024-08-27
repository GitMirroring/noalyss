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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 10/08/24
/*!
 * \file
 * \brief manage mini report
 * \note this widget is  included in Noalyss Core and a part of the code (javascript + css + ajax)
 *   are still included in NOALYSS Code, this code should move here and will be part of a "cleansing code" process
 */
namespace Noalyss\Widget;

use HtmlInput;
use Periode;
/*!
 * \class
 * \brief display simple report in a widget on the DASHBOARD
 * \note this widget is  included in Noalyss Core and a part of the code (javascript + css + ajax)
 *  are still included in NOALYSS Code, this code should move here and will be part of a "cleansing code" process
 */
class Mini_Report extends Widget
{
    /**
     * @brief  show the simple report from USER_WIDGET.WD_PARAMETER
     * @return void
     * @throws \Exception
     */
    function display()
    {
        global $g_user, $cn;

        $param = $this->db->get_value("select uw_parameter from user_widget where uw_id=$1",[$this->user_widget_id]);
        parse_str($param, $aReport);
        $report=$aReport['simple_report'];
        $rapport = new \Acc_Report($cn, $report);

        if ($rapport->exist() == false) {
            $report = 0;
        }
        $this->open_div();
        if ($report != 0) {
            $report_id=$this->get_div_domid();
            ?>
            <?php echo $this->title($rapport->get_name()) ;?>

            <?php
            $exercice = $g_user->get_exercice();
            if ($exercice == 0) {
                alert(_('Aucune periode par defaut'));
            } else {
                $periode = new Periode($cn);
                $limit = $periode->limit_year($exercice);

                $result = $rapport->get_row($limit['start'], $limit['end'], 'periode');
                $ix = 0;
                if (!empty ($result) && count($result) > 0) {
                    echo '<table class="result">';
                    foreach ($result as $row) {
                        $ix++;
                        $class = ($ix % 2 == 0) ? ' class="even" ' : ' class="odd" ';
                        echo '<tr ' . $class . '>';

                        echo '<td> ' . $row['desc'] . '</td>';
                        $style = 'style="text-align:right;"';
                        if ($row['montant'] < 0) {
                            $style = 'style="color:red;text-align:right;"';
                        }
                        echo "<td $style>" . nbm($row['montant']) . "</td>";
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo _('Aucun résultat');
                }
            }
        }
        $this->close_div();
    }

    /**
     * @brief select the simple report (FORM_DEFINITION) to display
     * @see Widget::make_form()
     * @return void
     */
    function input_parameter() {

        $select=new \ISelect('simple_report');
        $select->value=$this->db->make_array("select fr_id, fr_label from form_definition order by 2");

        $this->make_form($select->input());
    }

    /**
     * @brief Display the parameter of the form
     * @return void
     */
    function display_parameter() {
        $aParam= $this->get_parameter();
        $name = $this->db->get_value("select fr_label from form_definition where fr_id=$1",[$aParam['simple_report']]);
        echo " ";
        echo span(_("Rapport") ." ".h($name),'class="widget_param"');


    }

}