<?php
namespace Noalyss\XMLDocument;

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
 * @file
 * @brief Give the error message thanks the code for FacturX and UBL21
 * 
 */

/**
 * @class
 * @brief Give the error message thanks the code for FacturX and UBL21
 * 
  property $a_error (double array)
  keys :
  - general ,
  - customer,
        - ATTR_DEF_NAME=>'CUST_NAME'
        - ATTR_DEF_ADRESS=>'CUST_ADDR'
        - ATTR_DEF_POSTCODE=>'CUST_POSTCD'
        - ATTR_DEF_CITY=>'CUST_CITY'
        - ATTR_DEF_COUNTRY_CODE=>'CUST_CDCOUNTRY'
        - ATTR_DEF_NUMTVA=>'CUST_VAT'
        - ATTR_DEF_PEPPOLID=>'CUST_PEPPOLID'
        
  - company,
        - "INVOICE_EMAIL_COMPANY" company's email
        - 'INVOICE_CONTACT_NAME' contact name
        - 'COMPANY_LEGAL_ENTITY' legal form of company
        - 'COMPANY_LEGAL_REGISTRATION' full name
        - 'COMPANY_BANK_IBAN' IBAN bank account
        - 'COMPANY_BANK_BIC'  BIC bank account
        - 'COMPANY_UBL_ID'    PEPPOL id ==> normalement c'est BE0999999999
        - 'MY_COUNTRY_CODE'   country code (normally BE)
        - 'MY_NAME'           short company name
        - 'MY_STREET'         address
        - 'MY_CITY'           address
        - 'MY_TVA'            VAT number
 *      -  'SIREN'
 * 
 */
class Error_Message
{

    private $a_error;
    private $a_message_company;
    private $a_message_customer;

    /**
     * @brief constructo
     * @param $a_error (double array)
     */
    public function __construct($a_error)
    {
        $this->a_error = $a_error;
        $this->a_message_company = array(
            "INVOICE_EMAIL_COMPANY" => _("L'email de la société ")
            , 'INVOICE_CONTACT_NAME' => _("Nom du contact")
            , 'COMPANY_LEGAL_ENTITY' => _("Type de société (SRL,ASBL,...)")
            , 'COMPANY_LEGAL_REGISTRATION' => _("Nom complet de la société")
            , 'COMPANY_BANK_IBAN' => _("Compte en banque (IBAN) de la société")
            , 'COMPANY_BANK_BIC' => _("Code BIC de compte en banque")
            , 'COMPANY_UBL_ID' => _("Identifiant PEPPOL")
            , 'MY_COUNTRY_CODE' => _('Code Pays')
            , 'MY_NAME' => _("Nom de la société")
            , 'MY_STREET' => _("Adresse de la société")
            , 'MY_CITY' => _("Ville")
            , 'MY_TVA' => _("Numéro de TVA")
            , 'SIREN'=> 'SIREN'
       //     , 'SIRET'=> 'SIRET'
        );
        $this->a_message_customer = array(
            'name' => _("Nom")
            , 'street' => _("Adresse")
            , 'postalzone' => _("Code postal")
            , 'city' => _("Ville")
            , 'country'=>_("Code pays")
            , 'customer_vat_id' => _("Numéro de TVA")
            , 'endpoint_id' => _('Identifiant PEPPOL') // here 9925:BE....
            
        );
        
    }

    public function get_a_error()
    {
        return $this->a_error;
    }

    public function set_a_error($a_error)
    {
        $this->a_error = $a_error;
        return $this;
    }

    /**
     * @brief returns the text of an error
     * @param $code (string or number) 
     * @param $type (string) customer , general or company
     */
    function get_message_error($code, $type)
    {
        if ($type == "customer")
        {
            $a_error = $this->a_error['customer'];
            $a_message = $this->a_message_customer;
        } else if ($type == "company")
        {
            $a_error = $this->a_error['company'];
            $a_message = $this->a_message_company;
        }  else
        {
            throw new \Exception("EM116: unknow type");
        }

       return $a_message[$code];
    }
}
