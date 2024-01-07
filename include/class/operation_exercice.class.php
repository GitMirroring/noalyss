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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 6/01/24
/*!
 * \file
 * \brief
 */

class Operation_Exercice
{
    protected $operation_exercice_sql;

    public function __construct($p_id = -1)
    {
        $this->operation_exercice_sql = new Operation_Exercice_SQL(Dossier::connect(), $p_id);
    }

    /**
     * @brief input the source of the data : folder, exercice, closing or opening operation
     * @return void
     */
    public static function input_source()
    {
        require NOALYSS_TEMPLATE . "/operation_exercice-input_source.php";
    }

    function display_result()
    {
        $cn = Dossier::connect();
        // get data
        $a_data = $cn->get_array("
       SELECT oed_id
            , oe_id
            , oed_poste
            , oed_qcode
            , oed_label
            , oed_amount
            , oed_debit
       FROM public.operation_exercice_detail
       where 
           oe_id=$1
       order by oed_debit desc,oed_poste,oed_qcode

        ", [$this->operation_exercice_sql->oe_id]);
        $aheader = array(_("Poste"), _("Fiche"), _("Libellé"), _("Montant"), _("Débit/Crédit"));
        echo \HtmlInput::filter_table("operation_exercice_tb", '0,1,2,3,4', 1);
        echo \HtmlInput::button_action(_("Ajouter une ligne"), sprintf("operation_exercice.modify_row('-1','%s')", $this->operation_exercice_sql->oe_id));
        echo '<table class="result" id="operation_exercice_tb">';
        foreach ($aheader as $header) echo th($header, 'style="text-align:center"');
        echo th("");

        foreach ($a_data as $data) {
            $this->display_row($data);
        }
        echo '</table>';
        echo \HtmlInput::button_action(_("Ajouter une ligne"), sprintf("operation_exercice.modify_row('-1','%s')", $this->operation_exercice_sql->oe_id));
        $this->display_total();
        echo Dossier::hidden();
        $js = <<<EOF
(function() {
   $$(".op-exercice").forEach(item=>item.addEventListener("click",function(event) {operation_exercice.click_modify_row(item)}));
    })();
EOF;
        echo create_script($js);
    }

    /**
     * @brief display the balance (total) of the operation
     * @param bool $with_span if yes add the span wrapper , otherwise doesn't add it
     */
    public function display_total($with_span = true)
    {
        $cn = Dossier::connect();
        $sql_total = "
     with saldo_deb_cred as
(
	select
		case when oed_debit is true then oed_amount else 0-oed_amount end signed_amount ,
		case when oed_debit is true then oed_amount end debit,
		case when oed_debit is false then oed_amount end credit
	from public.operation_exercice_detail
	where oe_id=$1
)
select sum(signed_amount) delta,sum(debit) debit,sum(credit) credit from saldo_deb_cred
        ";
        $total = $cn->get_row($sql_total, [$this->operation_exercice_sql->oe_id]);
        if ($with_span) {
            echo '<span id="tot_ope_exe" style="margin-left:20%">';
        }
        $style = 'style="display:inline-block;padding:1rem;margin:1rem;border:1px solid navy;width:20%;text-align:center;font-size:140%"';

        echo span(sprintf(_("Débit   %s"), nbm($total['debit'])), $style);
        echo span(sprintf(_("Crédit  %s"), nbm($total['credit'])), $style);
        $s="";
        if ($total['delta'] > 0) {
            $s = " Solde débiteur ";
        }
        if ($total['delta'] < 0) {
            $s = " Solde créditeur ";
        }
        echo span($s . " " . nbm($total['delta']), $style);
        if ($with_span) {
            echo '</span>';
        }
    }

    /**
     * @brief let display one row
     * @param $data array row of operation_exercice_detail [oed_id, oe_id, oed_poste, oed_qcode oed_label
     * oed_amount oed_debit]
     * @return void
     * @see Operation_Exercice_Detail_SQL
     */
    function display_row($data, $row_tr = true)
    {
        if ($row_tr) printf('<tr class="op-exercice even" id="oe_%s" oed_id="%s" oe_id="%s">', $data['oed_id'], $data['oed_id'], $data['oe_id']);
        echo td($data['oed_poste']);
        echo td($data['oed_qcode']);
        echo td(h($data['oed_label']));
        echo td(nbm($data['oed_amount']), 'class="num"');
        echo td(($data['oed_debit'] == 'f' ? _("Crédit") : _("Débit")), 'style="text-align:center"');
        echo td(\Icon_Action::modify(uniqid(), sprintf("operation_exercice.modify_row('%s','%s')", $data['oed_id'], $data['oe_id'])));


        if ($row_tr) print ('</tr >');
    }

    /**
     * @brief input one row of operation_exercice
     * @param $data array row of operation_exercice_detail [oed_id, oe_id, oed_poste, oed_qcode oed_label
     * oed_amount oed_debit]
     * @return void
     * @see Operation_Exercice_Detail_SQL
     */
    public static function input_row(Operation_Exercice_Detail_SQL $operation_detail_sql)
    {
        require_once NOALYSS_TEMPLATE . "/operation_exercice-input_row.php";
    }

}
