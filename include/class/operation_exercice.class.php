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
 * \brief Special operations end or start of exercice
 */

/**
 * @class Operation_Exercice
 * @brief Special operations end or start of exercice
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
        $date = new IDate("exercice_date");
        $date->id = "exercice_date";
        $date->value = format_date($this->operation_exercice_sql->getp("oe_date"), "DD.MM.YYYY");
        $inplace_date = new Inplace_Edit($date);
        $inplace_date->add_json_param("op", "operation_exercice+date");
        $inplace_date->add_json_param("gDossier", Dossier::id());
        $inplace_date->add_json_param("oe_id", $this->operation_exercice_sql->oe_id);
        $inplace_date->set_callback("ajax_misc.php");
        echo _("Date"), $inplace_date->input();

        $text_operation = new IText("text_operation");
        $text_operation->id = uniqid("text");
        $text_operation->size = 80;
        $text_operation->value = $this->operation_exercice_sql->getp("oe_text");
        $inplace_text = new Inplace_Edit($text_operation);
        $inplace_text->add_json_param("op", "operation_exercice+text");
        $inplace_text->add_json_param("gDossier", Dossier::id());
        $inplace_text->add_json_param("oe_id", $this->operation_exercice_sql->oe_id);
        $inplace_text->set_callback("ajax_misc.php");
        echo $inplace_text->input();

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
        echo '<div></div>';
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
        $s = "";
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
        $operation=new Operation_Exercice_SQL($operation_detail_sql->get_cn(),$operation_detail_sql->getp("oe_id"));
        if ( $operation->getp("oe_transfer_date") !="") {
            require_once NOALYSS_TEMPLATE . "/operation_exercice-input_row-error.php";
            return;
        }

        require_once NOALYSS_TEMPLATE . "/operation_exercice-input_row.php";
    }

    /**
     * @brief input data for transfering
     * @return void
     */
    function input_transfer()
    {
        $operation=$this->operation_exercice_sql;
        if ( $operation->getp("oe_transfer_date") !="") {
            echo '<span class="warning">';
            printf(_("Opération transférée le %s")
                ,$operation->getp("oe_transfer_date") );
            echo '</span>';
            return;
        }
        require_once NOALYSS_TEMPLATE . "/operation_exercice-input_transfer.php";
    }

    /**
     * @brief transfer to accountancy
     * @param $ledger_id int the ledger id (jrn_def_id)
     * @return void
     */
    function submit_transfer($ledger_id)
    {
        global $oe_result; // result of operation
        global $oe_data; // transform data to array used by Acc_Ledger::insert
        global $oe_status ; // status OK or NOK
        $cn = Dossier::connect();

        $this->transform($ledger_id);

        $oe_status = "OK";
        $ledger = new Acc_Ledger($cn, $ledger_id);
        try {
            $cn->start();
            if ($this->operation_exercice_sql->getp("oe_transfer_date")!="") throw new \Exception("duplicate",EXC_DUPLICATE);
            if ( empty($oe_data['e_date']  ) ) throw new \Exception ("Date null",2);
            $ledger->verify_operation($oe_data);
            $ledger->save($oe_data);
            $oe_result=_("Détail opération");
            $oe_result.=sprintf('<a class="detail" style="display:inline" href="javascript:modifyOperation(%d,%d)">%s</a><hr>',
                $ledger->jr_id, dossier::id(), $ledger->internal);

            $cn->exec_sql("update operation_exercice set oe_transfer_date=to_timestamp($1,'DD.MM.YY HH24:MI') ,  jr_internal=$2 where oe_id=$3",
            [date('d.m.Y H:i'),$ledger->internal,$this->operation_exercice_sql->oe_id]);

            $cn->commit();
            return true;
        } catch (\Exception $e) {
            $oe_result=$e->getMessage();

            $oe_status='NOK';
            $cn->rollback();
        }
        return false;

    }

    /**
     * @brief Transform the data in table OPERATION_EXERCICE and OPERATION_EXERCICE_DETAIL into an array usable
     *  by Acc_Ledger, the result will be stored into the global variable $oe_data
     * @globals $oe_data array with the data transformed
     * @param $ledger_id
     * @return void
     * @throws Exception
     */
    function transform($ledger_id)
    {
        global $oe_data; // transform data to array used by Acc_Ledger::verify_operation
        $cn = Dossier::connect();
        $acc_ledger=new \Acc_Ledger($cn, $ledger_id);
        $oe_data = array();
        $oe_data['p_currency_code'] = 0;
        $oe_data['p_currency_rate'] = 1;
        $oe_data['p_jrn'] = $ledger_id;
        $oe_data['e_date'] = $this->operation_exercice_sql->oe_date;
        $oe_data['desc']=$this->operation_exercice_sql->getp("oe_text");
        $operation_detail_sql = new Operation_Exercice_Detail_SQL($cn);
        $all_operation = $operation_detail_sql->collect_objects(' where oe_id = $1',[$this->operation_exercice_sql->oe_id]);
        $nb = 0;
        foreach ($all_operation as $item) {
            $oe_data['qc_' . $nb] = $item->oed_qcode;
            $oe_data['poste' . $nb] = $item->oed_poste;
            $oe_data['ld' . $nb] = $item->oed_label;
            $oe_data['amount' . $nb] = $item->oed_amount;

            if ($item->oed_debit == "t") $oe_data['ck' . $nb] = 't';
            $nb++;
        }
        $oe_data['nb_item']=$nb;
        $oe_data['e_pj']=$acc_ledger->guess_pj();
        $oe_data['e_pj_suggest']=$acc_ledger->guess_pj();
        $oe_data['mt']=microtime(true);
        $oe_data['jr_optype']=($this->operation_exercice_sql->getp('oe_type')=='opening')?'OPE':'CLO';

    }

    public static function list_draft()
    {

        require_once NOALYSS_TEMPLATE."/operation_exercice-list_draft.php";

    }

    public static function delete($aOperation_id)
    {
        $cn=Dossier::connect();
        foreach ($aOperation_id as $operation_id)
        {
            $cn->exec_sql("delete from operation_exercice where oe_id=$1",[$operation_id]);
        }
    }

    public function get_operation_exercice_sql(): Operation_Exercice_SQL
    {
        return $this->operation_exercice_sql;
    }

    public function set_operation_exercice_sql(Operation_Exercice_SQL $operation_exercice_sql): Operation_Exercice_SQL
    {
        $this->operation_exercice_sql = $operation_exercice_sql;
        return $this;

    }


}
