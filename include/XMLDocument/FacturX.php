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

use \Kinulab\Facturx\CrossIndustryInvoice as KINU_FX1;
use \Atgp\FacturX as FX_ATGP;
/**
 * @file
 * @brief answer to an inplace object
 */
class FacturX extends XMLInvoice
{
     const EXTRA_PARAMETER = ["INVOICE_EMAIL_COMPANY"
        , 'INVOICE_CONTACT_NAME'
        , 'COMPANY_LEGAL_ENTITY'
        , 'COMPANY_LEGAL_REGISTRATION'
        , 'COMPANY_BANK_IBAN'
        , 'COMPANY_BANK_BIC'
        , 'COMPANY_UBL_ID'
        , 'COUNTRY_CODE'
        , 'MY_NAME'
        , 'MY_STREET'
        , 'MY_CITY'
        , 'MY_COUNTRY_CODE'
        , 'MY_TVA'
        ,'SIREN'
        ,'SIRET'
        ];

    protected $pdf_filename;
    
    function build_data($jr_id): array {
        $result = parent::build_data($jr_id);
        
        
        $customer=new \Fiche($this->cn,$result['customer']['card_id']);
        $result['customer']['siren']=$customer->get_attribute(ATTR_DEF_SIREN);
        $result['customer']['siret']=$customer->get_attribute(ATTR_DEF_SIRET);
        return $result;
    }
     /**
     * @brief check that mandatory info are saved in the DB
     * @param $a_error (array) array of errors, empty if nothing found
     */
    function check_company_data(&$a_error) {
        echo "not implemented";
        return true;
    }
     /**
     * @brief check that mandatory info are saved in the DB for customer
     * @param $customer_id (int) card of the customer  FICHE.F_ID
     * @param $a_error (array) array of errors, empty if nothing found
     */
    function check_customer_data($customer_id,&$a_error){
        echo "not implemented";
        return true;
        
    }
  
     /**
     * @brief create an XML invoice(Factur-X) based on JRN.JR_ID operation
     * @parameter $jr_id (int) operation JRN.JR_ID operation
      *@return XML String
      *@note SIREN or SIRET is mandatory 
     */
    function make_xml($jr_id)
    {
        $this->data = $this->build_data($jr_id);
        $invoice= new KINU_FX1\CrossIndustryInvoice(KINU_FX1\CrossIndustryInvoice::PROFILE_BASIC_WL);
        $invoice->setInvoiceNumber($this->data['id']);
        $invoice->setInvoiceType(KINU_FX1\CrossIndustryInvoice::INVOICE_TYPE_COMMERCIAL_INVOICE);
        $invoice->setIssueDate(\DateTime::createFromFormat( 'Y-m-d',$this->data['issue_date']));
       
        if ( $this->data['due_date'] !="") {
            $invoice->setDueDate(\DateTime::createFromFormat( 'Y-m-d',$this->data['due_date']));
        }else {
            $due_date=\DateTime::createFromFormat( 'Y-m-d',$this->data['issue_date']);
            $due_date->modify('+ 30 days');
            $invoice->setDueDate($due_date);
            
        }
        $supplier=new KINU_FX1\LegalEntity();
        $company = $this->load_noalyss_parameter();
        $supplier->setName($company['MY_NAME']);
        $supplier->setSiren($company['SIREN']);
         $supplier->setSiret($company['SIRET']);
        //$supplier->setSiren('999999');
        $supplier->setVatIdentifier($company['MY_TVA']);
        $supplier_addres=new KINU_FX1\Address();
        $supplier_addres->setCityName($company['MY_CITY'])
            ->setCountryId($company['MY_COUNTRY_CODE'])
            ->setCityName($company['MY_CITY'])
            ->setLines($company['MY_STREET']);
        $supplier->setAddress($supplier_addres);
        $invoice->setPaymentInstruction(null);
        $invoice->setPaymentMeansCode(0);
        $invoice->setSeller($supplier);
        $invoice->setBuyer(new KINU_FX1\LegalEntity);
        $buyer=$invoice->getBuyer();
        $buyer->setName($this->data['customer']['name']);
        $buyer->setSiren($this->data['customer']['siren']);
        $buyer->setSiret($this->data['customer']['siret']);
        $buyer->setVatIdentifier($this->data['customer']['customer_id']);
        $buyer->setAddress(new KINU_FX1\Address());
        $address=$buyer->getAddress();
        $address->setLines($this->data['customer']['street'])
                ->setCityName($this->data['customer']['city'])
                ->setZipCode($this->data['customer']['postalzone'])
                ->setCountryId($this->data['customer']['country']);
        
        $invoice->setCurrencyCode('EUR');
        $base=0;$vat=0;
        $nb=count($this->data['operation']);
        ///@note : Pour l'autoliquidation le total TVA  = 0
        for ($i=0;$i < $nb;$i++) {
            $base=bcadd($base,$this->data['operation'][$i]['price'],2);
            $vat=bcadd($vat,$this->data['operation'][$i]['vat'],2);
            $vat=bcsub($vat,$this->data['operation'][$i]['vat_reversed'],2);
        }
        $tt  = bcadd($base,$vat,2);
        /**
         * @note : Le total de la facture n'est pas toujours le total du.
         * il faut alors un "reste" à payer.
         * Pas de détail par articles ?
         */
        $invoice->setTaxBasisTotalAmount($base);
        $invoice->setTaxTotalAmount($vat);
        $invoice->setGrandTotalAmount($tt);
        $invoice->setDuePayableAmount($tt);
        $xml = KINU_FX1\XmlWriter::write($invoice);
        return $xml;
    }
    /**
     * @brief create the invoice in the right format
     * @param $operation_id (int) JRN.JR_ID
     * @return string PDF Invoice including the XML
     */
    function create_invoice($operation_id) {
        $xml = $this->make_xml($operation_id);
        $facturx = new FX_ATGP\Facturx();
        $invoice=$facturx->generateFacturxFromFiles($this->pdf_filename, $xml);
        return $invoice;
    }

}