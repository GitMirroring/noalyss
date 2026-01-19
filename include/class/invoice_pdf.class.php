<?php

namespace Noalyss;

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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 22/10/23


/**
 * @file invoice_pdf.class.php
 * @brief create a standard invoice
 */

/**
 * @class Invoice_PDF 
 * @brief create a standard invoice
 */
class Invoice_PDF extends \PDF
{

    private $data; //!< $data (array) see Acc_Ledger_Purchases

    function __construct(
            \Database $cn
            , private $dirname //!< folder where to save file
            , private $filename //!< filename to use
    )
    {
        parent::__construct($cn);
    }

    public function get_dirname()
    {
        return $this->dirname;
    }

    public function get_filename()
    {
        return $this->filename;
    }

    public function set_dirname($dirname)
    {
        $this->dirname = $dirname;
        return $this;
    }

    public function set_filename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    function set_data($array)
    {
        $this->data = $array;
        return $this;
    }

    function get_data()
    {
        return $this->data;
    }

    function footer()
    {
        //Position at 1 cm from bottom
        $this->SetY(-10);
        //Arial italic 8
        $this->SetFont('Arial', '', 8);
        //Page number
        parent::Cell(0, 8, " Page " . $this->PageNo() . '/{nb}', 0, 0, 'C');
        parent::Ln(3);
    }

    function header()
    {
        global $g_parameter;
        $this->setY(15);
        $this->SetFont('DejaVu', '', 6);
        $colsize = 90;
        $this->write_multi($colsize, 3, $g_parameter->MY_NAME);
        $this->write_multi($colsize, 3, $this->data['e_date'], border: '', align: 'R');
        $this->line_new();
        $this->write_multi($colsize, 3,
                sprintf("%s %s "
                        , $g_parameter->MY_STREET
                        , $g_parameter->MY_NUMBER));
        $this->line_new();
        $this->write_multi($colsize, 3, $g_parameter->MY_POSTCODE
                . " " . $g_parameter->MY_CITY
                . " " . $g_parameter->MY_COUNTRY
        );
        $this->line_new();
        $this->write_multi($colsize, 3, $g_parameter->MY_TVA);
        $this->line_new();

        $email_company = $this->cn->get_value("select pe_value from parameter_extra where pe_code=$1",
                ['INVOICE_EMAIL_COMPANY']);
        $site = $this->cn->get_value("select pe_value from parameter_extra where pe_code=$1",
                ['WEB_COMPANY']);
        // for FRANCE , the SIREN and SIRET must be given
        $siren = $this->cn->get_value("select pe_value from parameter_extra where pe_code=$1",
                ['SIREN']);
        $siret = $this->cn->get_value("select pe_value from parameter_extra where pe_code=$1",
                ['SIRET']);
        $iban = $this->cn->get_value("select pe_value from parameter_extra where pe_code=$1",
                ['COMPANY_BANK_IBAN']);
        $bic = $this->cn->get_value("select pe_value from parameter_extra where pe_code=$1",
                ['COMPANY_BANK_BIC']);

        if ($siret != "")
        {
            $this->write_multi($colsize, 3, "SIRET $siret");
            $this->line_new();
        }
        if ($siren != "")
        {
            $this->write_multi($colsize, 3, "SIREN $siren");
            $this->line_new();
        }
        if ($iban != "")
        {
            $this->write_multi($colsize, 3, "IBAN $iban BIC $bic");
            $this->line_new();
        }
        if ($g_parameter->MY_PHONE != "")
        {
            $this->write_multi($colsize, 3, sprintf(_("Tel %s "),
                            $g_parameter->MY_PHONE
                    ));
            $this->line_new();
        }
        if ($email_company != "")
        {
            $this->write_multi($colsize, 3, sprintf(_("email %s "),
                            $email_company));
            $this->line_new();
        }
        if ($site != "")
        {
            $this->write_multi($colsize, 3, sprintf(_("site %s"),
                            $site
                    ));
            $this->line_new();
        }
        $this->setFont("DejaVu", 'B', 14);
        $this->write_multi(40, 10, "");
        $this->write_multi(100, 10, _("Facture") . " " . $this->data['e_pj'], border: 1, align: 'C');
        $this->write_multi(40, 10, "");
        $this->line_new(10);
        $this->ln(5);
    }

    //! 
    //@brief make the invoice
    function export()
    {
        $this->SetAuthor('NOALYSS');
        $this->AliasNbPages();
        $this->AddPage();
        $this->SetAutoPageBreak(true, $this->bMargin*1);
        $this->setTitle($this->filename, true);
        // $customer (Fiche) retrieve card of the customer
        $customer = new \Fiche($this->cn);
        $customer->get_by_qcode(trim($this->data['e_client']));

        $this->setFont("DejaVu", '', 7);
        $this->write_cell(50, 4, "");
        $this->write_cell(60, 4, sprintf(_("Echéance %s"), $this->data['e_ech']));
        $this->line_new();
        $this->write_cell(50, 4, "");
        $this->write_cell(100, 4, _("Client"), 'B', 'R');
        $this->line_new();
        $this->write_cell(50, 4, "");
        $this->write_cell(110, 4, $customer->get_attribute(ATTR_DEF_NAME)
                . " " . $customer->get_attribute(ATTR_DEF_FIRST_NAME, 0));
        $this->line_new();
        $this->write_cell(50, 4, "");
        $this->write_cell(110, 4, $customer->get_attribute(ATTR_DEF_ADRESS, 0));
        $this->line_new();
        $this->write_cell(50, 4, "");
        $this->write_cell(110, 4, $customer->get_attribute(ATTR_DEF_POSTCODE, 0)
                . " " . $customer->get_attribute(ATTR_DEF_CITY, 0)
        );
        $this->line_new();
        $this->write_cell(50, 4, "");
        $this->write_cell(110, 4, $customer->get_attribute(ATTR_DEF_COUNTRY, 0));
        $this->line_new();
        $this->write_cell(50, 4, "");
        $this->write_cell(110, 4, $customer->get_attribute(ATTR_DEF_NUMTVA, 0));
        $this->line_new();
        $a_tva_amount = [];
        $a_tva_code = [];
        $col = array(
            "quick_code" => 30,
            "label" => 80,
            "quantity" => 25,
            "price" => 25,
            "vat_code" => 20
        );
        $this->SetFont("DejaVu", "B", 12);
        $this->write_multi(50, 20, "");
        $this->write_multi(50, 20, _("Détails"));
        $this->line_new();
        $this->SetFont("DejaVu", "B", 7);
        $currency = new \Acc_Currency($this->cn, $this->data['p_currency_code']);
        $this->write_multi(100, 4, sprintf(_("Les montants sont en %s taux %s")
                        , $currency->get_code()
                        , $this->data['p_currency_rate']));
        $this->line_new(4);
        $this->SetFont("DejaVuCond", "", 7);
        if ($this->data["bon_comm"] != "")
        {
            $this->write_multi(120, 4, sprintf(_("Bon de commande / référence %s")
                            , $this->data["bon_comm"]));
            $this->line_new(4);
        }
        $this->line_new(4);
        $this->SetFont("DejaVu", "", 7);
        $this->write_multi($col['quick_code'], 4, _("Article"), 1);
        $this->write_multi($col['label'], 4, _("Description"), 1);
        $this->write_multi($col['quantity'], 4, _("Quantité"), 1, align: 'C');
        $this->write_multi($col['price'], 4, _("Prix"), 1, align: 'C');
        $this->write_multi($col['vat_code'], 4, _("TVA"), 1, align: 'C');
        $this->line_new();
        ///@var $tot_amount (float) total amount without VAT
        ///@var $tot_vat (float) total VAT
        ///@var $line (int) line printed
        $tot_amount = $tot_vat = $line =0;
        for ($i = 0; $i < $this->data['nb_item']; $i++)
        {
            $item = new \Fiche($this->cn);
            if (!isset($this->data['e_march' . $i]) || $this->data['e_march' . $i] == "")
            {
                continue;
            }
            $line++;
            $item->get_by_qcode(trim($this->data['e_march' . $i]));
            $fill  = $this->is_fill($line);
            $this->write_multi($col['quick_code'], 4, $item->get_attribute(ATTR_DEF_QUICKCODE),'','',$fill);
            if ( isset ($this->data['e_march' . $i . '_label']))
            {
                $this->write_multi($col['label'], 4, $this->data['e_march' . $i . '_label'],fill:$fill);
            }else {
                $this->write_multi($col['label'], 4,$item->get_attribute(ATTR_DEF_NAME),fill:$fill);
            }
            $this->write_multi($col['quantity'], 4, nbm($this->data['e_quant' . $i]), '', 'R',fill:$fill);
            $this->write_multi($col['price'], 4, nbm($this->data['e_march' . $i . '_price']), '', 'R',fill:$fill);
            if (isset ($this->data['e_march' . $i . '_tva_id']))
            {
                $this->write_multi($col['vat_code'], 4, $this->data['e_march' . $i . '_tva_id'], '', 'C',fill:$fill);
                $x = $this->data['e_march' . $i . '_tva_id'];
                if (!isset($a_tva_amount[$x]))
                {
                    $a_tva_amount[$x] = 0;
                }
                $a_tva_amount[$x] = bcadd($a_tva_amount[$x], $this->data["e_march" . $i . "_tva_amount"], 2);
                $tot_vat = bcadd($tot_vat
                                    , $this->data['e_march' . $i . '_tva_amount']
                            , 2);
            }
            $tot_amount = bcadd($tot_amount
                                    , bcmul($this->data['e_march' . $i . '_price']
                                            , $this->data['e_quant' . $i]
                                            , 2
                                    )
                                , 2);
            $this->line_new(4);
            if ($this->GetY()>250) {
                $this->AddPage();
            }
        }
        $this->line_new(10);
        $this->SetFont("DejaVu", "B", 9);
        $this->write_multi(30, 4, _("TVA"));
        $this->line_new(5);
        $this->SetFont("DejaVu", "", 7);
        foreach ($a_tva_amount as $tva_id => $tva_amount)
        {
            $tva = \Acc_Tva::build($this->cn, $tva_id);
            $this->write_multi(20, 4, "");
            $this->write_multi(80, 4, $tva->tva_id
                    . " / " . $tva->tva_code
                    . " / " . $tva->tva_label
                    . " / " . $tva->tva_rate * 100
            );

            $this->write_multi(50, 4, $tva_amount,align:'R');
            $this->line_new();
        }
        $this->ln(20);
        $this->SetFont("DejaVu", "B", 9);
        $this->write_multi(30, 4, _("TOTAUX"));
        $this->line_new();
        $this->SetFont("DejaVu", "", 7);
        $this->write_multi(60, 4, _("Total Hors TVA "));
        $this->write_multi(60, 4, nbm($tot_amount), '', 'R');
        $this->line_new();
        $this->write_multi(60, 4, _("Total TVA "));
        $this->write_multi(60, 4, nbm($tot_vat), '', 'R');
        $this->line_new();
        $this->write_multi(60, 4, _("Total "));
        $this->write_multi(60, 4, nbm(bcadd($tot_amount, $tot_vat, 2),2), '', 'R');
        $this->line_new();
        $iban = $this->cn->get_value("select pe_value from parameter_extra where pe_code=$1",
                ['COMPANY_BANK_IBAN']);
        if ($this->data['e_ech'] != "" && $iban != "")
        {
            $info = ($this->data["other_info"] == "") ? $this->data["e_pj"] : $this->data["other_info"];
            $bic = $this->cn->get_value("select pe_value from parameter_extra where pe_code=$1",
                    ['COMPANY_BANK_IBAN']);
            $this->write_multi(150, 4,
                    sprintf(_("Paiement avant le %s sur le compte %s (BIC %s) avec comme message %s"),
                            $this->data['e_ech']
                            , $iban
                            , $bic
                            , $this->data["other_info"]
                    )
            );
            $this->line_new();
        }
        $this->Output($this->dirname . DIRECTORY_SEPARATOR . $this->filename, "F");
    }
}
