<?php

/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2002-2021) Author Dany De Bontridder <danydb@noalyss.eu>

/**
 * @file
 * @brief manage the operation in currency : export CSV, export PDF , output in HTML
 */
require_once NOALYSS_INCLUDE."/class/acc_currency.class.php";
require_once NOALYSS_INCLUDE."/class/data_currency_operation.class.php";
require_once NOALYSS_INCLUDE."/class/filter_data_currency_card.class.php";
require_once NOALYSS_INCLUDE."/class/filter_data_currency_accounting.class.php";
require_once NOALYSS_INCLUDE."/class/filter_data_currency_card_category.class.php";
require_once NOALYSS_INCLUDE."/lib/noalyss_csv.class.php";
/**
 * @class
 * @brief manage the operation in currency : export CSV , output in HTML
 */
class Print_Operation_Currency
{

    private $data_operation;
   

    function __construct(Data_Currency_Operation $data_operation)
    {
        $this->data_operation=$data_operation;
    }

   

    /**
     * @brief build a Print_Operation_Currency Object thanks the http request ($_REQUEST) with the right Filter 
     * @param string $p_search possible values are "by_card" if we filter by card, by_account if we 
     * filter by accounting, by_category or empty if we take everything
     */
    static function build($p_search)
    {
        $http=new HttpInput();
        $from_account=$http->request("from_account", "string", "");
        $to_account=$http->request("to_account", "string", "");
        $currency_code=$http->request("p_currency_code", "string", "0");
        $from_date=$http->request("from_date", "date", "");
        $to_date=$http->request("to_date", "date", "");
        $card=$http->request("card","string","");
        $card_category_id=$http->request("card_category_id","string","0");
        $cn=Dossier::connect();
        if ($p_search=='by_card')
        {
            $data_operation=new Filter_Data_Currency_Card($cn,$from_date,$to_date,$currency_code,$card);
        }
        elseif ($p_search=='by_accounting')
        {
            $data_operation=new Filter_Data_Currency_Accounting($cn,$from_date,
                    $to_date,$currency_code,$from_account,$to_account);
        }
        elseif ($p_search=="by_category")
        {
            $data_operation=new Filter_Data_Currency_Card_Category($cn,$from_date,$to_date,
                    $currency_code,$card_category_id);
        }
        elseif ($p_search=="all")
        {
            $data_operation=new Data_Currency_Operation ($cn,$from_date,$to_date,$currency_code);
        }
        else
        {
            throw new Exception("PROC67 Invalid parameter");
        }

        $print_operation_currency=new Print_Operation_Currency($data_operation);
        return $print_operation_currency;
    }
    /**
     * @brief return Data_Currency_Operation with a possible filter
     * @return type
     */
    public function getData_operation()
    {
        return $this->data_operation;
    }

    /**
     * @brief return Data_Currency_Operation with a possible filter
     * @return type
     */
    public function setData_operation($data_operation)
    {
        $this->data_operation=$data_operation;
    }
    /**
     * @brief Display in HTML
     * @return string
     */
    function export_html()
    { 
        $aData=$this->data_operation->get_data();
        if ( empty ($aData)){
            return h2(_("Aucune donnée"),'class="error"');
        }
        $date=_("Date");
        $accounting=_("Poste comptable");
        $card_qcode=_("Fiche");
        $receipt=_("Pièce");
        $internal=_("Internal");
        $comment=_("Libellé");
        $amount=_("Montant");
        $amount_currency=_("Mont. Devise");
        $rate_ref=_("Taux de référence");
        $rate=_("Taux utilisé");
        $currency=_("Devise");
        $r=HtmlInput::filter_table("pcur01_tb", '0,1,2,3,4,5,6,7,8,9,10', 1);
        $r.=<<<EOF
<table id="pcur01_tb" class="result">
<tr>
                <th>{$date}</th>
                <th>{$accounting}</th>
                <th>{$card_qcode}</th>
                <th>{$receipt}</th>
                <th>{$comment}</th>
                <th>{$internal}</th>
                <th>{$currency}</th>
                <th class="num">{$amount}</th>
                <th class="num">{$rate}</th>
                <th class="num">{$rate_ref}</th>
                <th class="num">{$amount_currency}</th>
</tr>    

EOF;
        $nb_data=count($aData);
        for ($i=0;$i<$nb_data;$i++)
        {
            $r.="<tr>";
            $r.=td($aData[$i]['j_date']);
            $r.=td($aData[$i]['j_poste']);
            $r.=td($aData[$i]['fiche_qcode']);
            $r.=td($aData[$i]['jr_pj_number']);
            $r.=td($aData[$i]['jr_comment']);
            $r.="<td>";
            $r.=HtmlInput::detail_op($aData[$i]['jr_id'], $aData[$i]['jr_internal']);
            $r.="</td>";
            $r.=td($aData[$i]['currency_code_iso']);
            $r.=td(nbm($aData[$i]['j_montant'],2),'class="num"');
            $r.=td(round($aData[$i]['currency_rate'],4),'class="num"');
            $r.=td(round($aData[$i]['currency_rate_ref'],4),'class="num"');
            $r.=td(nbm($aData[$i]['oc_amount'],2),'class="num"');
            $r.="</tr>";
        }
        $r.="</table>";
        return $r;
        
    }
    /**
     * @brief Output in CSV
     */
    function export_csv(Noalyss_CSV $export)
    {
        $aData=$this->data_operation->get_data();
        
        $date=_("Date");
        $accounting=_("Poste comptable");
        $card_qcode=_("Fiche");
        $receipt=_("Pièce");
        $internal=_("Internal");
        $comment=_("Libellé");
        $amount=_("Montant");
        $amount_currency=_("Mont. Devise");
        $rate_ref=_("Taux de référence");
        $rate=_("Taux utilisé");
        $currency=_("Devise");
        
        $export->write_header(array ($date,
            $accounting,
            $card_qcode,
            $receipt,
            $comment,
            $currency,
            $internal,
            $amount,
            $rate,
            $rate_ref,
            $amount_currency));
        $nb_data=count($aData);
        for ($i=0;$i<$nb_data;$i++)
        {
            $export->add($aData[$i]['j_date']);
            $export->add($aData[$i]['j_poste']);
            $export->add($aData[$i]['fiche_qcode']);
            $export->add($aData[$i]['jr_pj_number']);
            $export->add($aData[$i]['jr_comment']);
            $export->add($aData[$i]['jr_internal']);
            $export->add($aData[$i]['currency_code_iso']);
            $export->add(nbm($aData[$i]['j_montant'],2),"number");
            $export->add(round($aData[$i]['currency_rate'],4),"number");
            $export->add(round($aData[$i]['currency_rate_ref'],4),"number");
            $export->add(nbm($aData[$i]['oc_amount'],2),"number"); 
            $export->write();
        }
    }

}

?>
